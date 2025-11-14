@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('kualifinSelector', (hierarchy) => ({
                hierarchy,
                selectedExecutiveId: '',
                selectedSupervisorId: '',
                selectedPromoterId: '',
                get executives() {
                    return this.hierarchy;
                },
                get supervisors() {
                    const executive = this.executives.find((item) => item.id === this.selectedExecutiveId);
                    return executive ? executive.supervisores : [];
                },
                get promoters() {
                    const supervisor = this.supervisors.find((item) => item.id === this.selectedSupervisorId);
                    return supervisor ? supervisor.promotores : [];
                },
                get selectedExecutive() {
                    return this.executives.find((item) => item.id === this.selectedExecutiveId) || null;
                },
                get selectedSupervisor() {
                    return this.supervisors.find((item) => item.id === this.selectedSupervisorId) || null;
                },
                get selectedPromoter() {
                    const promoter = this.promoters.find((item) => item.id === this.selectedPromoterId);
                    return promoter ? promoter : null;
                },
                get clients() {
                    return this.selectedPromoter ? this.selectedPromoter.clientes : [];
                },
                get calendarColumns() {
                    const promoter = this.selectedPromoter;
                    if (!promoter || !promoter.calendar_columns) {
                        return [];
                    }
                    return promoter.calendar_columns;
                },
                onExecutiveChange() {
                    if (!this.supervisors.some((item) => item.id === this.selectedSupervisorId)) {
                        this.selectedSupervisorId = '';
                    }
                    this.onSupervisorChange();
                },
                onSupervisorChange() {
                    if (!this.promoters.some((item) => item.id === this.selectedPromoterId)) {
                        this.selectedPromoterId = '';
                    }
                },
                labelFor(person) {
                    const username = person.usuario ? ` - ${person.usuario}` : '';
                    return `${person.nombre} (${person.codigo})${username}`;
                },
                totalValue(key) {
                    const promoter = this.selectedPromoter;
                    if (!promoter || !promoter.totals) {
                        return '';
                    }
                    return promoter.totals[key] ?? '';
                },
                financialValue(key) {
                    const promoter = this.selectedPromoter;
                    if (!promoter || !promoter.financial_summary) {
                        return '';
                    }
                    return promoter.financial_summary[key] ?? '';
                },
                calendarTotalGlobal() {
                    const promoter = this.selectedPromoter;
                    if (!promoter || !promoter.calendar_total_global) {
                        return '';
                    }
                    return promoter.calendar_total_global;
                },
                matrixDisplay(client, key) {
                    if (!client || !client.pagos_por_fecha_filtrados) {
                        return '';
                    }
                    const cell = client.pagos_por_fecha_filtrados[key];
                    if (!cell) {
                        return '';
                    }
                    if (typeof cell === 'object' && cell.display) {
                        return cell.display;
                    }
                    if (typeof cell === 'number') {
                        return cell.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
                    }
                    return '';
                },
                matrixCellClass(client, key) {
                    if (!client || !client.pagos_por_fecha_filtrados) {
                        return 'text-center text-[11px] text-gray-300';
                    }
                    const cell = client.pagos_por_fecha_filtrados[key];
                    if (!cell) {
                        return 'text-center text-[11px] text-gray-300';
                    }
                    const base = 'text-center text-[11px] font-semibold';
                    if (typeof cell === 'object') {
                        return `${base} ${cell.is_future ? 'bg-amber-100 text-amber-700' : 'bg-amber-200 text-amber-900'}`;
                    }
                    return `${base} bg-amber-100 text-amber-700`;
                },
                calendarTotalClass(column) {
                    return column && column.is_future ? 'bg-blue-50 text-blue-700' : 'bg-blue-100 text-blue-800';
                },
            }));
        });
    </script>
@endpush
