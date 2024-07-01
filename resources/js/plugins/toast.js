import Swal from 'sweetalert2'

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 5000,
  timerProgressBar: true,
  showCloseButton: true,
  didOpen: toast => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  },
})

export function showToast(message, type) {
  Toast.fire({
    icon: type,
    title: message,
  })
}

const ToastCenter = Swal.mixin({
  backdrop: true,
  position: "center",
  showConfirmButton: true,
  timer: 3000,
  timerProgressBar: true,
  didOpen: el => {
    el.addEventListener("mouseenter", Swal.stopTimer)
    el.addEventListener("mouseleave", Swal.resumeTimer)
  },
})

export function showToastCenter(message, type) {
  ToastCenter.fire({
    icon: type,
    title: message,
  })
}

export default Swal
