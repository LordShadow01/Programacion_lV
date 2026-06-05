import Swal from 'sweetalert2';

// Creamos un mixin pre-configurado de SweetAlert2 con las clases de diseño de Mango Music
export const mangoSwal = Swal.mixin({
  customClass: {
    popup: 'mango-swal2-popup',
    title: 'mango-swal2-title',
    htmlContainer: 'mango-swal2-text',
    actions: 'mango-swal2-actions',
    confirmButton: 'mango-swal2-confirm-btn',
    cancelButton: 'mango-swal2-cancel-btn'
  }
});

export default mangoSwal;
