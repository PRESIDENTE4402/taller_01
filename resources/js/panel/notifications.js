document.addEventListener('DOMContentLoaded', () => {
    const markAllReadForm = document.getElementById('markAllReadForm');
    
    if (markAllReadForm) {
        markAllReadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: '¿Marcar todas como leídas?',
                text: "Ya no aparecerán resaltadas como notificaciones nuevas.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#ef4444',
                confirmButtonText: '<i class="fas fa-check"></i> Sí, marcar todas',
                cancelButtonText: 'Cancelar',
                background: '#ffffff',
                color: '#1f2937',
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-gray-100',
                    confirmButton: 'btn btn-primary text-white border-none',
                    cancelButton: 'btn btn-ghost'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    markAllReadForm.submit();
                }
            });
        });
    }
});
