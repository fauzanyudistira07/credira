import './bootstrap';
import Alpine from 'alpinejs';

const formatCurrency = (value) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
}).format(value || 0);

const debounce = (callback, delay = 300) => {
    let timeoutId;

    return (...args) => {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => callback(...args), delay);
    };
};

const parseHTML = (markup) => new DOMParser().parseFromString(markup, 'text/html');

const cleanParams = (formData) => {
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        if (value === null || value === undefined) {
            continue;
        }

        const normalized = String(value).trim();

        if (normalized !== '') {
            params.set(key, normalized);
        }
    }

    return params;
};

const createRipple = (event, button) => {
    const circle = document.createElement('span');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const rect = button.getBoundingClientRect();

    circle.style.width = `${diameter}px`;
    circle.style.height = `${diameter}px`;
    circle.style.left = `${event.clientX - rect.left - diameter / 2}px`;
    circle.style.top = `${event.clientY - rect.top - diameter / 2}px`;
    circle.style.position = 'absolute';
    circle.style.borderRadius = '9999px';
    circle.style.pointerEvents = 'none';
    circle.style.background = 'rgba(255,255,255,0.35)';
    circle.style.transform = 'scale(0)';
    circle.style.animation = 'ripple 0.55s ease-out forwards';

    button.append(circle);
    window.setTimeout(() => circle.remove(), 650);
};

const buildSkeletonCards = (count = 6) => {
    const cards = Array.from({ length: count }, () => `
        <div class="skeleton-card">
            <div class="skeleton-line h-48 w-full rounded-[1.3rem]"></div>
            <div class="mt-5 space-y-3">
                <div class="skeleton-line h-3 w-24"></div>
                <div class="skeleton-line h-6 w-2/3"></div>
                <div class="skeleton-line h-5 w-1/2"></div>
                <div class="skeleton-line h-11 w-full rounded-2xl"></div>
            </div>
        </div>
    `);

    return cards.join('');
};

const readFilePreview = (file, callback) => {
    if (!file || !file.type.startsWith('image/')) {
        callback(null);
        return;
    }

    const reader = new FileReader();
    reader.onload = () => callback(reader.result);
    reader.readAsDataURL(file);
};

document.addEventListener('click', (event) => {
    const drawerToggle = event.target.closest('[data-drawer-toggle]');

    if (drawerToggle) {
        const target = document.querySelector(drawerToggle.dataset.drawerToggle);
        target?.classList.toggle('hidden');
        return;
    }

    const button = event.target.closest('.btn-primary, .btn-secondary, .btn-accent, .btn-ghost');

    if (button) {
        createRipple(event, button);
    }
});

document.addEventListener('alpine:init', () => {
    Alpine.store('toast', {
        items: [],
        push(toast) {
            const id = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            this.items.push({
                id,
                title: toast.title || 'Informasi',
                message: toast.message || '',
                type: toast.type || 'success',
            });

            window.setTimeout(() => this.remove(id), toast.duration || 4200);
        },
        remove(id) {
            this.items = this.items.filter((item) => item.id !== id);
        },
        bootFromWindow() {
            const payloads = window.__CREDIRA_TOASTS || [];
            payloads.forEach((toast) => this.push(toast));
            window.__CREDIRA_TOASTS = [];
        },
    });

    Alpine.data('asyncList', (config = {}) => ({
        loading: false,
        skeletonCount: config.skeletonCount || 6,
        init() {
            this.form = this.$refs.form;
            this.results = this.$refs.results;
            this.pagination = this.$refs.pagination;
            this.chips = this.$refs.chips;

            const debouncedFetch = debounce(() => this.fetchResults(), 320);

            this.form.querySelectorAll('input, select').forEach((field) => {
                if (field.type === 'text' || field.type === 'search' || field.type === 'number') {
                    field.addEventListener('input', debouncedFetch);
                }

                field.addEventListener('change', () => this.fetchResults());
            });

            this.$el.addEventListener('click', (event) => {
                const paginationLink = event.target.closest('a');

                if (paginationLink && this.pagination.contains(paginationLink)) {
                    event.preventDefault();
                    this.fetchResults(paginationLink.href);
                    return;
                }

                const chipRemove = event.target.closest('[data-chip-remove]');

                if (chipRemove) {
                    event.preventDefault();
                    const field = this.form.querySelector(`[name="${chipRemove.dataset.chipRemove}"]`);

                    if (field) {
                        if (field.tagName === 'SELECT') {
                            field.selectedIndex = 0;
                        } else {
                            field.value = '';
                        }

                        this.fetchResults();
                    }
                }
            });

            this.renderChips();
        },
        fieldLabel(field) {
            const baseLabel = field.dataset.chipLabel || field.name;

            if (field.tagName === 'SELECT') {
                const option = field.options[field.selectedIndex];
                return option && option.value !== '' ? `${baseLabel}: ${option.textContent.trim()}` : null;
            }

            return field.value.trim() !== '' ? `${baseLabel}: ${field.value.trim()}` : null;
        },
        renderChips() {
            if (!this.chips) {
                return;
            }

            const chipItems = [...this.form.querySelectorAll('[data-chip-label]')]
                .map((field) => ({
                    name: field.name,
                    label: this.fieldLabel(field),
                }))
                .filter((chip) => chip.label);

            if (chipItems.length === 0) {
                this.chips.innerHTML = '';
                return;
            }

            this.chips.innerHTML = chipItems.map((chip) => `
                <button type="button" class="filter-chip" data-chip-remove="${chip.name}">
                    <span>${chip.label}</span>
                    <span aria-hidden="true">x</span>
                </button>
            `).join('');
        },
        setFieldsFromUrl(url) {
            const params = new URL(url).searchParams;

            this.form.querySelectorAll('input, select').forEach((field) => {
                if (!field.name) {
                    return;
                }

                const nextValue = params.get(field.name) || '';

                if (field.tagName === 'SELECT') {
                    field.value = nextValue;
                } else {
                    field.value = nextValue;
                }
            });
        },
        async fetchResults(targetUrl = null) {
            this.loading = true;
            this.results.innerHTML = buildSkeletonCards(this.skeletonCount);
            this.pagination.innerHTML = '';

            try {
                const url = targetUrl
                    ? new URL(targetUrl, window.location.origin)
                    : new URL(window.location.pathname, window.location.origin);

                if (!targetUrl) {
                    url.search = cleanParams(new FormData(this.form)).toString();
                } else {
                    this.setFieldsFromUrl(url);
                }

                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Filter request failed.');
                }

                const documentFragment = parseHTML(await response.text());
                const nextResults = documentFragment.querySelector('[data-async-results]');
                const nextPagination = documentFragment.querySelector('[data-async-pagination]');

                this.results.innerHTML = nextResults ? nextResults.innerHTML : '';
                this.pagination.innerHTML = nextPagination ? nextPagination.innerHTML : '';
                this.renderChips();
                window.history.replaceState({}, '', url);
            } catch (error) {
                Alpine.store('toast').push({
                    type: 'error',
                    title: 'Gagal memuat data',
                    message: 'Coba ulangi beberapa saat lagi.',
                });
            } finally {
                this.loading = false;
            }
        },
    }));

    Alpine.data('applicationWizard', (config = {}) => ({
        step: config.initialStep || 1,
        totalSteps: 5,
        lastSavedAt: null,
        previews: {},
        init() {
            this.form = this.$refs.form;

            if (!config.hasOldInput && !config.applicationId) {
                this.restoreDraft();
            }

            this.bindAutosave();
            this.bindFilePreview();
            this.bindAddressPrefill();
            this.bindSubmitValidation();
        },
        progress() {
            return `${(this.step / this.totalSteps) * 100}%`;
        },
        isCurrent(step) {
            return this.step === step;
        },
        isComplete(step) {
            return this.step > step;
        },
        goNext() {
            if (!this.validateStep(this.step)) {
                return;
            }

            this.step = Math.min(this.step + 1, this.totalSteps);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        goPrev() {
            this.step = Math.max(this.step - 1, 1);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        validateStep(step) {
            const panel = this.$refs[`step${step}`];

            if (!panel) {
                return true;
            }

            const processedRadioNames = new Set();
            const requiredFields = [...panel.querySelectorAll('[data-step-required]')].filter((field) => {
                if (field.type !== 'radio') {
                    return true;
                }

                if (processedRadioNames.has(field.name)) {
                    return false;
                }

                processedRadioNames.add(field.name);
                return true;
            });

            const isInvalidField = (field) => {
                if (field.type === 'checkbox') {
                    return !field.checked;
                }

                if (field.type === 'radio') {
                    return !panel.querySelector(`[name="${field.name}"]:checked`);
                }

                return !field.value;
            };

            const firstInvalid = requiredFields.find((field) => isInvalidField(field));

            requiredFields.forEach((field) => {
                const invalid = isInvalidField(field);

                if (field.type === 'radio') {
                    panel.querySelectorAll(`[name="${field.name}"]`).forEach((radio) => {
                        radio.setAttribute('aria-invalid', invalid ? 'true' : 'false');
                    });
                    return;
                }

                field.setAttribute('aria-invalid', invalid ? 'true' : 'false');
            });

            if (firstInvalid) {
                firstInvalid.reportValidity();
                firstInvalid.focus();
                return false;
            }

            return true;
        },
        bindAutosave() {
            const saveDraft = debounce(() => {
                const draft = {
                    step: this.step,
                    fields: {},
                    timestamp: new Date().toISOString(),
                };
                const processedRadioNames = new Set();

                [...this.form.elements].forEach((field) => {
                    if (!field.name || ['_token', '_method'].includes(field.name) || field.type === 'file') {
                        return;
                    }

                    if (field.type === 'radio') {
                        if (processedRadioNames.has(field.name)) {
                            return;
                        }

                        processedRadioNames.add(field.name);
                        draft.fields[field.name] = this.form.querySelector(`[name="${field.name}"]:checked`)?.value || '';
                        return;
                    }

                    if (field.type === 'checkbox') {
                        draft.fields[field.name] = field.checked;
                        return;
                    }

                    draft.fields[field.name] = field.value;
                });

                window.localStorage.setItem(config.storageKey, JSON.stringify(draft));
                this.lastSavedAt = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            }, 500);

            [...this.form.elements].forEach((field) => {
                if (!field.name || ['_token', '_method'].includes(field.name)) {
                    return;
                }

                field.addEventListener('input', () => {
                    field.removeAttribute('aria-invalid');
                    saveDraft();
                });

                field.addEventListener('change', () => {
                    field.removeAttribute('aria-invalid');
                    saveDraft();
                });
            });
        },
        restoreDraft() {
            const cachedDraft = window.localStorage.getItem(config.storageKey);

            if (!cachedDraft) {
                return;
            }

            try {
                const parsed = JSON.parse(cachedDraft);

                Object.entries(parsed.fields || {}).forEach(([name, value]) => {
                    const field = this.form.elements.namedItem(name);

                    if (!field) {
                        return;
                    }

                    if (field instanceof RadioNodeList) {
                        if (!field.value && value !== undefined && value !== null) {
                            field.value = value;
                        }
                        return;
                    }

                    if (field.type === 'checkbox') {
                        field.checked = Boolean(value);
                        return;
                    }

                    if (field.value) {
                        return;
                    }

                    field.value = value;
                });

                this.step = parsed.step || 1;
                this.lastSavedAt = new Date(parsed.timestamp).toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                });
            } catch (_error) {
                window.localStorage.removeItem(config.storageKey);
            }
        },
        bindAddressPrefill() {
            const addressField = this.form.querySelector('[name="alamat_pengiriman_id"]');

            if (!addressField) {
                return;
            }

            addressField.addEventListener('change', () => {
                const address = config.addresses?.[addressField.value];

                if (!address) {
                    return;
                }

                const mappings = {
                    alamat_lengkap: address.alamat_lengkap,
                    kota: address.kota,
                    provinsi: address.provinsi,
                    kode_pos: address.kode_pos,
                };

                Object.entries(mappings).forEach(([fieldName, value]) => {
                    const field = this.form.querySelector(`[name="${fieldName}"]`);

                    if (field) {
                        field.value = value || '';
                    }
                });
            });
        },
        bindFilePreview() {
            this.form.querySelectorAll('[data-file-preview-input]').forEach((input) => {
                input.addEventListener('change', () => {
                    const file = input.files?.[0];
                    const previewKey = input.dataset.previewKey;

                    if (!file || !previewKey) {
                        delete this.previews[previewKey];
                        return;
                    }

                    readFilePreview(file, (previewUrl) => {
                        this.previews[previewKey] = {
                            name: file.name,
                            previewUrl,
                        };
                    });
                });
            });
        },
        bindSubmitValidation() {
            this.form.addEventListener('submit', (event) => {
                const submitMode = event.submitter?.value || 'submit';

                if (submitMode === 'draft') {
                    return;
                }

                for (const step of [1, 2, 3, 4, 5]) {
                    if (!this.validateStep(step)) {
                        event.preventDefault();
                        this.step = step;
                        Alpine.store('toast').push({
                            type: 'error',
                            title: 'Lengkapi data terlebih dahulu',
                            message: 'Masih ada field wajib yang belum diisi pada tahap pengajuan.',
                        });
                        break;
                    }
                }
            });
        },
    }));
});

const bindSimulationForms = () => {
    document.querySelectorAll('[data-simulation-form]').forEach((form) => {
        if (form.dataset.boundSimulation === 'true') {
            return;
        }

        form.dataset.boundSimulation = 'true';
        const output = document.querySelector(form.dataset.simulationTarget);
        const submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!output) {
                return;
            }

            output.innerHTML = `
                <div class="grid gap-4 sm:grid-cols-2">
                    ${Array.from({ length: 4 }, () => `
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <div class="skeleton-line h-3 w-28"></div>
                            <div class="skeleton-line mt-4 h-7 w-40"></div>
                        </div>
                    `).join('')}
                </div>
            `;

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Menghitung...';
            }

            try {
                const formData = new FormData(form);
                const payload = Object.fromEntries(formData.entries());
                const response = await fetch('/api/public/simulation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(payload),
                });

                if (!response.ok) {
                    throw new Error('Simulation failed.');
                }

                const data = await response.json();
                const result = data.data;

                output.innerHTML = `
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pokok Kredit</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">${formatCurrency(result.pokok_kredit)}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Angsuran per Bulan</p>
                            <p class="mt-2 text-xl font-semibold text-slate-900">${formatCurrency(result.angsuran_per_bulan)}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Biaya Admin</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">${formatCurrency(result.biaya_admin)}</p>
                        </div>
                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Total Bayar</p>
                            <p class="mt-2 text-lg font-semibold text-slate-900">${formatCurrency(result.total_bayar)}</p>
                        </div>
                    </div>
                `;
            } catch (_error) {
                output.innerHTML = '<div class="rounded-3xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">Simulasi belum dapat diproses. Periksa kembali data yang dimasukkan.</div>';
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = submitButton.dataset.label || 'Hitung';
                }
            }
        });
    });
};

const bindFilePreviewInputs = () => {
    document.querySelectorAll('[data-file-preview-input]').forEach((input) => {
        if (input.dataset.previewBound === 'true') {
            return;
        }

        input.dataset.previewBound = 'true';
        const previewTarget = document.querySelector(input.dataset.previewTarget);

        input.addEventListener('change', () => {
            const files = [...(input.files || [])];

            if (!previewTarget) {
                return;
            }

            if (!files.length) {
                previewTarget.innerHTML = '';
                return;
            }

            Promise.all(files.map((file) => new Promise((resolve) => {
                readFilePreview(file, (previewUrl) => resolve({ file, previewUrl }));
            }))).then((items) => {
                previewTarget.innerHTML = items.map(({ file, previewUrl }) => `
                    <div class="file-upload-preview__item">
                        <p class="file-upload-preview__title">${file.name}</p>
                        <p class="file-upload-preview__meta">${Math.round(file.size / 1024)} KB</p>
                        ${previewUrl ? `<img src="${previewUrl}" alt="${file.name}" class="mt-4 h-44 w-full rounded-2xl object-cover">` : ''}
                    </div>
                `).join('');
            });
        });
    });
};

const bindSubmitButtons = () => {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.submitBound === 'true') {
            return;
        }

        form.dataset.submitBound = 'true';

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            const button = event.submitter;

            if (!(button instanceof HTMLButtonElement) || button.dataset.loadingApplied === 'true') {
                return;
            }

            button.dataset.loadingApplied = 'true';
            button.disabled = true;
            button.innerHTML = button.dataset.loadingText || 'Memproses...';
        });
    });
};

const bindUploadForms = () => {
    document.querySelectorAll('[data-upload-form]').forEach((form) => {
        if (form.dataset.uploadBound === 'true') {
            return;
        }

        form.dataset.uploadBound = 'true';
        const progressShell = form.querySelector('[data-upload-progress]');
        const progressBar = form.querySelector('[data-upload-bar]');
        const progressText = form.querySelector('[data-upload-text]');

        form.addEventListener('submit', (event) => {
            const fileInputs = [...form.querySelectorAll('input[type="file"]')];
            const hasFiles = fileInputs.some((input) => input.files && input.files.length > 0);

            if (!hasFiles || !progressShell || !progressBar || !progressText) {
                return;
            }

            event.preventDefault();
            progressShell.classList.remove('hidden');

            const xhr = new XMLHttpRequest();
            xhr.open(form.method || 'POST', form.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', (uploadEvent) => {
                if (!uploadEvent.lengthComputable) {
                    return;
                }

                const percentage = Math.round((uploadEvent.loaded / uploadEvent.total) * 100);
                progressBar.style.width = `${percentage}%`;
                progressText.textContent = `Mengunggah ${percentage}%`;
            });

            xhr.addEventListener('load', () => {
                progressBar.style.width = '100%';
                progressText.textContent = 'Menyelesaikan...';
                window.location = xhr.responseURL || window.location.href;
            });

            xhr.addEventListener('error', () => {
                progressShell.classList.add('hidden');
                Alpine.store('toast').push({
                    type: 'error',
                    title: 'Upload gagal',
                    message: 'Coba unggah ulang file beberapa saat lagi.',
                });
            });

            xhr.send(new FormData(form));
        });
    });
};

const bindViewportReveal = () => {
    const items = document.querySelectorAll('[data-reveal]');

    if (!items.length) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        items.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.14,
        rootMargin: '0px 0px -40px 0px',
    });

    items.forEach((item) => observer.observe(item));
};

const bindHomeSectionObserver = () => {
    const body = document.body;
    const sections = document.querySelectorAll('[data-section]');

    if (!body.hasAttribute('x-data') || !sections.length || !window.Alpine) {
        return;
    }

    const setActiveSection = (value) => {
        body._x_dataStack?.[0] && (body._x_dataStack[0].activeSection = value);
    };

    setActiveSection(sections[0].id || 'hero');

    if (!('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        const visibleEntry = entries
            .filter((entry) => entry.isIntersecting)
            .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

        if (visibleEntry?.target?.id) {
            setActiveSection(visibleEntry.target.id);
        }
    }, {
        threshold: [0.2, 0.35, 0.6],
        rootMargin: '-25% 0px -45% 0px',
    });

    sections.forEach((section) => observer.observe(section));
};

const bindHorizontalDataSliders = () => {
    const sliders = document.querySelectorAll('.admin-table-wrap, .marketing-table-wrap, .ceo-table-wrap');

    sliders.forEach((slider) => {
        slider.classList.add('data-scroll-slider');
        let hintDismissed = false;
        let dragStarted = false;

        const dismissHint = () => {
            if (hintDismissed) {
                return;
            }

            hintDismissed = true;
            slider.classList.add('is-hint-dismissed');
        };

        const updateScrollState = () => {
            const maxScroll = slider.scrollWidth - slider.clientWidth;

            slider.classList.toggle('is-scroll-idle', maxScroll <= 8);
            slider.classList.toggle('is-scroll-end', maxScroll > 8 && slider.scrollLeft >= maxScroll - 8);

            if (slider.scrollLeft > 8) {
                dismissHint();
            }
        };

        let isPointerDown = false;
        let startX = 0;
        let startScrollLeft = 0;

        slider.addEventListener('pointerdown', (event) => {
            if (event.target.closest('a, button, input, select, textarea, label, [role="button"]')) {
                return;
            }

            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            isPointerDown = true;
            dragStarted = false;
            startX = event.clientX;
            startScrollLeft = slider.scrollLeft;
            dismissHint();
            slider.setPointerCapture?.(event.pointerId);
        });

        slider.addEventListener('pointermove', (event) => {
            if (!isPointerDown) {
                return;
            }

            const delta = event.clientX - startX;

            if (Math.abs(delta) > 6) {
                dragStarted = true;
            }

            slider.scrollLeft = startScrollLeft - delta;
        });

        slider.addEventListener('click', (event) => {
            if (!dragStarted) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            dragStarted = false;
        }, true);

        const stopPointerDrag = (event) => {
            if (!isPointerDown) {
                return;
            }

            isPointerDown = false;
            slider.releasePointerCapture?.(event.pointerId);
            window.setTimeout(() => {
                dragStarted = false;
            }, 0);
        };

        slider.addEventListener('pointerup', stopPointerDrag);
        slider.addEventListener('pointercancel', stopPointerDrag);
        slider.addEventListener('touchstart', dismissHint, { passive: true });
        slider.addEventListener('scroll', updateScrollState, { passive: true });
        window.addEventListener('resize', updateScrollState);
        updateScrollState();
    });
};

document.addEventListener('DOMContentLoaded', () => {
    Alpine.start();
    Alpine.store('toast').bootFromWindow();
    bindSimulationForms();
    bindFilePreviewInputs();
    bindUploadForms();
    bindViewportReveal();
    bindHomeSectionObserver();
    bindHorizontalDataSliders();
    bindSubmitButtons();
});
