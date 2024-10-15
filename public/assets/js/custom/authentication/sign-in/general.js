"use strict";

var KTSigninGeneral = (function () {
    var form, submitButton, formValidation;

    return {
        init: function () {
            // Get form and submit button
            form = document.querySelector("#kt_sign_in_form");
            submitButton = document.querySelector("#kt_sign_in_submit");

            // Initialize form validation
            formValidation = FormValidation.formValidation(form, {
                fields: {
                    email: {
                        validators: {
                            regexp: {
                                regexp: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                                message:
                                    "The value is not a valid email address",
                            },
                            notEmpty: {
                                message: "Email address is required",
                            },
                        },
                    },
                    password: {
                        validators: {
                            notEmpty: {
                                message: "The password is required",
                            },
                        },
                    },
                },
                plugins: {
                    trigger: new FormValidation.plugins.Trigger(),
                    bootstrap: new FormValidation.plugins.Bootstrap5({
                        rowSelector: ".fv-row",
                        eleInvalidClass: "",
                        eleValidClass: "",
                    }),
                },
            });

            // Check if the form's action is a valid URL
            if (
                !isValidUrl(submitButton.closest("form").getAttribute("action"))
            ) {
                submitButton.addEventListener("click", function (event) {
                    event.preventDefault();
                    formValidation.validate().then(function (status) {
                        if (status === "Valid") {
                            // Show loading indicator
                            submitButton.setAttribute(
                                "data-kt-indicator",
                                "on"
                            );
                            submitButton.disabled = true;

                            // Simulate a successful login with a delay
                            setTimeout(function () {
                                submitButton.removeAttribute(
                                    "data-kt-indicator"
                                );
                                submitButton.disabled = false;

                                Swal.fire({
                                    text: "You have successfully logged in!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn btn-primary",
                                    },
                                }).then(function (result) {
                                    if (result.isConfirmed) {
                                        form.querySelector(
                                            '[name="email"]'
                                        ).value = "";
                                        form.querySelector(
                                            '[name="password"]'
                                        ).value = "";
                                        var redirectUrl = form.getAttribute(
                                            "data-kt-redirect-url"
                                        );

                                        // Delay before redirecting to the dashboard
                                        if (redirectUrl) {
                                            setTimeout(function () {
                                                location.href = redirectUrl;
                                            }, 100);
                                        }
                                    }
                                });
                            }, 2000); // Simulated delay before showing success message
                        } else {
                            Swal.fire({
                                text: "Sorry, looks like there are some errors detected, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                },
                            });
                        }
                    });
                });
            } else {
                // Handle form submission using axios
                submitButton.addEventListener("click", function (event) {
                    event.preventDefault();
                    formValidation.validate().then(function (status) {
                        if (status === "Valid") {
                            submitButton.setAttribute(
                                "data-kt-indicator",
                                "on"
                            );
                            submitButton.disabled = true;

                            axios
                                .post(
                                    submitButton
                                        .closest("form")
                                        .getAttribute("action"),
                                    new FormData(form)
                                )
                                .then(function (response) {
                                    if (response) {
                                        form.reset();
                                        Swal.fire({
                                            text: "You have successfully logged in!",
                                            icon: "success",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton:
                                                    "btn btn-primary",
                                            },
                                        });

                                        const redirectUrl = form.getAttribute(
                                            "data-kt-redirect-url"
                                        );

                                        if (redirectUrl) {
                                            setTimeout(function () {
                                                location.href = redirectUrl;
                                            }, 2000);
                                        }
                                    } else {
                                        Swal.fire({
                                            text: "Sorry, the email or password is incorrect, please try again.",
                                            icon: "error",
                                            buttonsStyling: false,
                                            confirmButtonText: "Ok, got it!",
                                            customClass: {
                                                confirmButton:
                                                    "btn btn-primary",
                                            },
                                        });
                                    }
                                })
                                .catch(function (error) {
                                    Swal.fire({
                                        text: "Sorry, looks like there are some errors detected, please try again.",
                                        icon: "error",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn btn-primary",
                                        },
                                    });
                                })
                                .finally(function () {
                                    submitButton.removeAttribute(
                                        "data-kt-indicator"
                                    );
                                    submitButton.disabled = false;
                                });
                        } else {
                            Swal.fire({
                                text: "Sorry, looks like there are some errors detected, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn btn-primary",
                                },
                            });
                        }
                    });
                });
            }
        },
    };

    // Utility function to validate URLs
    function isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch (error) {
            return false;
        }
    }
})();

// Initialize when DOM is ready
KTUtil.onDOMContentLoaded(function () {
    KTSigninGeneral.init();
});
