document.addEventListener('alpine:init', () => {
    Alpine.store('calc', {
        show: false,
        mode: null,
        amount: '',
        client: '',
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
            } = options;

            this.client = clientName || alternativeName || name || '';
            this.mode = initialMode ?? null;
            this.amount = initialAmount === undefined || initialAmount === null ? '' : String(initialAmount);
            this.context = context;
            this.onAccept = typeof onAccept === 'function' ? onAccept : null;
            this.show = true;
        },
        close() {
            this.show = false;
            this.mode = null;
            this.amount = '';
            this.client = '';
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
        accept() {
            const payload = {
                mode: this.mode,
                amount: this.amount,
                context: this.context,
                client: this.client,
            };

            if (this.onAccept) {
                this.onAccept(payload);
            }

            this.close();
        }
    });
});
