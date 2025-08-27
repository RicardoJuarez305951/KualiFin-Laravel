document.addEventListener('alpine:init', () => {
    Alpine.store('calc', {
        show: false,
        mode: null,
        amount: '',
        client: '',
        open(name) {
            this.client = name;
            this.mode = null;
            this.amount = '';
            this.show = true;
        },
        close() {
            this.show = false;
            this.mode = null;
            this.amount = '';
            this.client = '';
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
            this.close();
        }
    });
});
