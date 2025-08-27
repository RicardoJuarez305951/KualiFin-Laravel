document.addEventListener('alpine:init', () => {
    Alpine.store('details', {
        show: false,
        data: {},
        open(d) {
            this.data = d || {};
            this.show = true;
        },
        close() {
            this.show = false;
            this.data = {};
        }
    });
});
