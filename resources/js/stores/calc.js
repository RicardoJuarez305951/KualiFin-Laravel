document.addEventListener('alpine:init', () => {
    Alpine.store('calc', {
        show: false,
        mode: null,
        amount: '',
        client: '',
        selectedClient: null,
        context: null,
        onAccept: null,
        open(target, maybeOptions = {}) {
            let options = {};

            if (typeof target === 'object' && target !== null && !Array.isArray(target)) {
                options = target;
            } else {
                options = { ...maybeOptions, client: target };
            }

            const {
                client: clientName = '',
                name = '',
                clientName: alternativeName = '',
                initialMode = null,
                initialAmount = '',
                context = null,
                onAccept = null,
                clientData = null,
            } = options;

            this.client = clientName || alternativeName || name || '';
            this.mode = initialMode ?? null;
            this.amount = initialAmount === undefined || initialAmount === null ? '' : String(initialAmount);
            this.context = context;
            this.onAccept = typeof onAccept === 'function' ? onAccept : null;
            this.selectedClient =
                clientData && typeof clientData === 'object' && !Array.isArray(clientData)
                    ? { ...clientData }
                    : null;
            this.show = true;
        },
        close() {
            this.show = false;
            this.mode = null;
            this.amount = '';
            this.client = '';
            this.selectedClient = null;
            this.context = null;
            this.onAccept = null;
        },
        setMode(m) {
            this.mode = m;
            if (m === 'full') {
                this.accept();
            }
        },
        addDigit(d) {
            this.amount += String(d);
        },
        delDigit() {
            this.amount = this.amount.slice(0, -1);
        },
        normalizeNumber(value) {
            if (typeof value === 'number') {
                return Number.isFinite(value) ? value : null;
            }

            if (typeof value === 'string') {
                const sanitized = value.replace(/[^0-9,.-]+/g, '').replace(',', '.');
                if (!sanitized.length) {
                    return null;
                }

                const numeric = Number(sanitized);
                return Number.isFinite(numeric) ? numeric : null;
            }

            return null;
        },
        accept() {
            const payload = {
                mode: this.mode,
                amount: this.amount,
                context: this.context,
                client: this.client,
                clientId:
                    this.selectedClient?.id ??
                    this.context?.clientId ??
                    this.context?.clienteId ??
                    this.context?.id ??
                    null,
            };

            const contextMode = this.context?.mode ?? null;
            let shouldClose = true;

            if (contextMode === 'multiPay') {
                const multiPayStore = typeof Alpine !== 'undefined' ? Alpine.store('multiPay') : null;

                if (multiPayStore && typeof multiPayStore.applyCalculatorSubmission === 'function') {
                    const selectedClientId =
                        this.selectedClient?.id ??
                        this.context?.clientId ??
                        this.context?.clienteId ??
                        this.context?.id ??
                        null;

                    const tipo = this.mode === 'deferred' ? 'diferido' : 'completo';
                    const monto = tipo === 'diferido' ? this.normalizeNumber(this.amount) : null;

                    const handled = multiPayStore.applyCalculatorSubmission({
                        clienteId: selectedClientId,
                        tipo,
                        monto,
                    });

                    if (handled === false) {
                        shouldClose = false;
                    }
                }
            }

            if (shouldClose && this.onAccept) {
                this.onAccept(payload);
            }

            if (shouldClose) {
                this.close();
            }
        }
    });
});
