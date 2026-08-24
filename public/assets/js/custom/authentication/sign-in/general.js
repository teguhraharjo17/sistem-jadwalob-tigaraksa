"use strict";

var KTSigninGeneral = function () {
    var form;
    var submitButton;
    var validator;

    var handleValidation = function () {
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'username': {
                        validators: {
                            notEmpty: {
                                message: 'Username wajib diisi!'
                            }
                        }
                    },
                    'password': {
                        validators: {
                            notEmpty: {
                                message: 'Password wajib diisi!'
                            }
                        }
                    }
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: '.fv-row'
                    })
                }
            }
        );
    }

    var handleSubmitAjax = function () {
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            validator.validate().then(function (status) {
                if (status === 'Valid') {
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;

                    axios.post(form.getAttribute('action'), new FormData(form))
                        .then(function (response) {
                            form.reset();
                            const redirectUrl = form.getAttribute('data-kt-redirect-url');
                            if (redirectUrl) {
                                location.href = redirectUrl;
                            }
                        })
                        .catch(function (error) {
                            let title = "Gagal Masuk";
                            let message = "Terjadi kesalahan saat mencoba masuk. Silakan coba lagi.";
                            let icon = "error";
                            let focusInput = null;

                            if (error.response && error.response.status === 422 && error.response.data?.errors) {
                                const errors = error.response.data.errors;

                                if (errors.username) {
                                    message = errors.username[0];
                                    focusInput = 'username';
                                    if (message.includes('terdaftar') || message.includes('tidak terdaftar')) {
                                        title = "Username Tidak Ditemukan";
                                        icon = "warning";
                                    } else if (message.includes('percobaan') || message.includes('detik')) {
                                        title = "Akses Ditangguhkan";
                                        icon = "warning";
                                    }
                                } else if (errors.password) {
                                    message = errors.password[0];
                                    title = "Password Salah";
                                    icon = "error";
                                    focusInput = 'password';
                                } else {
                                    message = Object.values(errors).flat()[0];
                                }
                            }

                            Swal.fire({
                                title: title,
                                text: message,
                                icon: icon,
                                confirmButtonText: "Coba Lagi",
                                buttonsStyling: false,
                                customClass: {
                                    confirmButton: "btn btn-primary px-5"
                                }
                            }).then(function () {
                                if (focusInput) {
                                    const inputElem = form.querySelector(`input[name="${focusInput}"]`);
                                    if (inputElem) {
                                        inputElem.focus();
                                        inputElem.select();
                                    }
                                }
                            });
                        })
                        .finally(function () {
                            submitButton.removeAttribute('data-kt-indicator');
                            submitButton.disabled = false;
                        });
                } else {
                    Swal.fire({
                        title: "Form Belum Lengkap",
                        text: "Harap isi Username dan Password terlebih dahulu.",
                        icon: "warning",
                        confirmButtonText: "Mengerti",
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: "btn btn-primary px-5"
                        }
                    });
                }
            });
        });
    }

    var handlePasswordVisibilityToggle = function () {
        const visibilityToggles = document.querySelectorAll('[data-kt-password-meter-control="visibility"]');

        visibilityToggles.forEach(toggle => {
            const input = toggle.closest('.mb-3, .fv-row').querySelector('input');

            toggle.addEventListener('click', function () {
                const eyeIcon = toggle.querySelector('.bi-eye');
                const eyeSlashIcon = toggle.querySelector('.bi-eye-slash');

                if (input.getAttribute('type') === 'password') {
                    input.setAttribute('type', 'text');
                    eyeSlashIcon.classList.add('d-none');
                    eyeIcon.classList.remove('d-none');
                } else {
                    input.setAttribute('type', 'password');
                    eyeSlashIcon.classList.remove('d-none');
                    eyeIcon.classList.add('d-none');
                }
            });
        });
    }

    return {
        init: function () {
            form = document.querySelector('#kt_sign_in_form');
            submitButton = document.querySelector('#kt_sign_in_submit');

            handleValidation();
            handleSubmitAjax();
            handlePasswordVisibilityToggle();
        }
    };
}();

KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});

