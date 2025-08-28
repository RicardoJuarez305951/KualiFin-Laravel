document.addEventListener('alpine:init', () => {
    Alpine.store('details', {
        show: false,
        data: {},
        open(data) {
            this.data = data;
            this.show = true;
        },
        close() {
            this.show = false;
            this.data = {};
        },
    });
});

