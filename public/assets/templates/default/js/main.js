const csrfToken = $('meta[name="csrf-token"]').attr('content');

$(".popup-btn").click(function () {
    let popupContent = $(this).siblings(".popup-content");
    popupContent.toggle();
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': csrfToken
    }
});

const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

function handleButtonHeaders() {
    $(".btn-header").on("click", function () {
        const targetCollapse = $($(this).attr("href"));

        $(".collapse").not(targetCollapse).removeClass("show");
    });
}

function handleFormValidation() {
    $(".needs-validation").on("submit", function (event) {
        if (!this.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        $(this).addClass("was-validated");
    });
}

function togglePasswordVisibility() {
    $("#show_hide_password a").on("click", function (event) {
        event.preventDefault();
        const passwordInput = $("#show_hide_password input");

        passwordInput.attr("type", passwordInput.attr("type") === "text" ? "password" : "text");
        $("#show_hide_password i").toggleClass("fa-eye fa-eye-slash");
    });
}

function handlePhoneToggle() {
    $(".phone-toggle").on("click", function () {
        $(".hidden-phone").hide();
        $(".showed-phone").slideDown("slow");
    });
}

function handleCardDetails() {
    $(".showdetail").on("click", function () {
        let parentCard = $(this).closest(".card");
        parentCard.next().toggleClass("card-show-box-box");
    });
}

function handleHeartButton() {
    $('#heartButton').on('click', function () {
        $(this).toggleClass('red-heart');
    });
}

function handleCollapseEvents() {
    $('.collapse').on('show.bs.collapse', function () {
        $('.collapse.show').not(this).collapse('hide');
    });
}

function copyText() {
    var copyText = $("#myInput")[0];
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Текст скопирован: " + copyText.value);
}

function toggleSideMenu() {
    $(".toggle-side, .close-side").on("click", function () {
        $(".side-menu").toggleClass("show");
    });
}

function handleFaqContentCategories() {
    $('.faq-content-categories li').on('click', function () {
        $('.faq-content-categories li').removeClass('active');
        $(this).addClass('active');
    });
}

function switchPreview() {
    $('.preview').on('change', function () {
        const outputElement = $(this).closest('.axios-image').find('img')[0];
        outputElement.src = URL.createObjectURL(this.files[0]);
        outputElement.onload = function () {
            URL.revokeObjectURL(outputElement.src);
        };
    });
}

function localeSwitcher() {
    $('.lang').each(function () {
        $(this).on('click', function () {
            const lang = $(this).data('lang');
            changeLocale(lang);
        });
    });

    function changeLocale(selectedLanguage) {
        const form = $('<form>', {
            'action': '/change-locale',
            'method': 'GET'
        });

        const languageInput = $('<input>', {
            'type': 'hidden',
            'name': 'locale',
            'value': selectedLanguage
        });

        form.append(languageInput);

        $('body').append(form);

        form.submit();
    }
}

function toggleFavorite() {
    $('.toggle-favorite').each(function () {
        var currentToggle = $(this);

        currentToggle.on('click', function () {
            $.ajax({
                type: 'POST',
                url: '/favorites/toggle',
                data: {
                    'ad_id': currentToggle.data('ad-id')
                },
                success: function (data) {
                    const heartIcon = currentToggle.find('i');

                    if (data.added) {
                        heartIcon.removeClass('fa-regular');
                        heartIcon.addClass('fa-solid');
                    } else {
                        heartIcon.removeClass('fa-solid');
                        heartIcon.addClass('fa-regular');
                    }
                },
                error: function (xhr, status, error) {
                    Toast.fire({
                        icon: "error",
                        title: xhr.responseJSON.message
                    });
                },
            });
        })
    })
}

function clearFavorites() {
    $('.clear-favorites').on('click', function () {
        $.ajax({
            type: 'DELETE',
            url: '/favorites/clear', error: function (xhr, status, error) {
                Toast.fire({
                    icon: "error", title: xhr.responseJSON.message
                });
            }
        });
    })
}

function init() {
    handleButtonHeaders();
    handleFormValidation();
    togglePasswordVisibility();
    handlePhoneToggle();
    handleCardDetails();
    handleHeartButton();
    handleCollapseEvents();
    // copyText();
    switchPreview();
    toggleSideMenu();
    handleFaqContentCategories();
    localeSwitcher();
    toggleFavorite();
    clearFavorites();
}

init();
