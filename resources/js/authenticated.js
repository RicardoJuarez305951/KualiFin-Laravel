export default {
    init() {
        return {
            sidebarOpen: false,
            sidebarCollapsed: false,
            
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
            },
            
            toggleCollapse() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                // Guardar preferencia en localStorage
                localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
            },
            
            initSidebar() {
                // Recuperar preferencia guardada
                const savedState = localStorage.getItem('sidebarCollapsed');
                if (savedState !== null) {
                    this.sidebarCollapsed = JSON.parse(savedState);
                }
                
                // Cerrar sidebar en móviles al hacer click fuera
                document.addEventListener('click', (e) => {
                    if (this.sidebarOpen && !e.target.closest('aside') && !e.target.closest('button')) {
                        this.sidebarOpen = false;
                    }
                });
            }
        }
    }
}