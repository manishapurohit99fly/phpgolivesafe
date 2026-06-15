
$(window).scroll(function () {
    if ($(window).scrollTop() >= 40) {
        $('.top-header').addClass('fixedheader');
    }
    else {
        $('.top-header').removeClass('fixedheader');
    }
});




$(".slidetoggle").click(function () {
    $(".innerbody").toggleClass("menu-collapse");
});
$(".closemenu-btn").click(function () {
    $(".innerbody").removeClass("menu-collapse");
});


$('.menubar > li > a').click(function () {
    $(".menubar > li > a.active").removeClass('active');
    $(this).toggleClass('active');
});


$(document).ready(function () {
    // $('.js-example-basic-multiple').select2();

    // Apply spacing/formatting rules globally
    // No spaces at all (e.g., usernames)
    applySpaceRules('input.no-space', 'noSpace');

    // No leading or double spaces (e.g., names, titles)
    applySpaceRules('input.no-leading-space, textarea.no-leading-space', 'noLeadingSpace');

    // Single space and single line break (e.g., textareas for descriptions)
    applySingleSpaceSingleBreak('textarea.single-space-break');

    // Allow phone with plus and space (e.g., phone fields)
    allowPhoneWithPlusAndSpace('input.phone');

    // Numeric only (e.g., OTP, PIN, numbers)
    allowNumericOnly('input.numeric');

    // Limit input to 8 characters (e.g., short codes)
    limitrInput('input.limit8');

    // Alphanumeric only (e.g., codes, IDs)
    allowAlphaNumericOnly('input.alphanumeric');
});





let list = document.querySelector('.slider .list');
let items = document.querySelectorAll('.slider .list .item');
let dots = document.querySelectorAll('.slider .dots li');
let prev = document.getElementById('prev');
let next = document.getElementById('next');

let active = 0;
let lengthitems = items.length - 1;

// next.onclick = function() {
//     if (active + 1 > lengthitems) {
//         active = 0;
//     } else {
//         active = active + 1;
//     }
//     reloadslider();
// }

// prev.onclick = function() {
//     if (active - 1 < 0) {
//         active = lengthitems;
//     } else {
//         active = active - 1;
//     }
//     reloadslider();
// }

// let autoslide = setInterval(() => { next.click(); }, 3000);

function reloadslider() {
    let checkleft = items[active].offsetLeft;
    list.style.left = -checkleft + 'px';

    let lastactiveDot = document.querySelector('.slider .dots li.active');
    if (lastactiveDot) lastactiveDot.classList.remove('active');
    dots[active].classList.add('active');
}

dots.forEach((li, key) => {
    li.addEventListener('click', function () {
        active = key;
        reloadslider();
    })
})



// **************************

$(".admin-toastr").trigger("click");

// =============================================================================
// GLOBAL PASSWORD VISIBILITY TOGGLE
// -----------------------------------------------------------------------------
// Works for any `.toggle-password` element, in two layouts:
//  1) <div class="position-relative"><input type="password" /><span class="toggle-password"><i class="fa fa-eye"></i></span></div>
//  2) <div class="input-group"><input type="password" /><button class="toggle-password btn btn-outline-secondary"><i class="fa fa-eye"></i></button></div>
// Resolves the target input by:
//   - the closest .input-group, or
//   - the nearest sibling input, or
//   - explicit data-target="#selector".
// =============================================================================
// GLOBAL PASSWORD VISIBILITY TOGGLE
// -----------------------------------------------------------------------------
// Works for any `.toggle-password` element, in two layouts:
//  1) <div class="position-relative"><input type="password" /><span class="toggle-password"><i class="fa fa-eye"></i></span></div>
//  2) <div class="input-group"><input type="password" /><button class="toggle-password btn btn-outline-secondary"><i class="fa fa-eye"></i></button></div>
// Resolves the target input by:
//   - explicit data-target="#selector", or
//   - the closest .input-group, or
//   - the closest .position-relative wrapper, or
//   - the nearest sibling input.
// Note: .off() before .on() prevents duplicate handler registration across
//       layouts that load custom.js alongside page-specific scripts.
//       stopImmediatePropagation() ensures no other delegated click handler
//       interferes with the toggle.
// =============================================================================
$(document).off('click', '.toggle-password').on('click', '.toggle-password', function (e) {
    e.preventDefault();
    e.stopImmediatePropagation();

    var $btn   = $(this);
    var target = $btn.data('target');
    var $input;

    if (target) {
        $input = $(target);
    } else if ($btn.closest('.input-group').length) {
        $input = $btn.closest('.input-group').find('input').first();
    } else if ($btn.closest('.position-relative').length) {
        $input = $btn.closest('.position-relative').find('input').first();
    } else {
        $input = $btn.siblings('input').first();
        if (!$input.length) {
            $input = $btn.parent().find('input').first();
        }
    }

    if (!$input || !$input.length) return;

    var $icon = $btn.find('i');
    if ($input.attr('type') === 'password') {
        $input.attr('type', 'text');
        $icon.removeClass('fa-eye').addClass('fa-eye-slash');
    } else {
        $input.attr('type', 'password');
        $icon.removeClass('fa-eye-slash').addClass('fa-eye');
    }
});
function toastr_alert(heading, msg, type) {
    toastr[type](
        msg,
        {
            closeButton: true,
            progressBar: true,
        }
    );
}


// STATUS UPDATE AJAX

function updateStatus(id, model, url, _this) {
    const status = $(_this).prop('checked') == true ? 1 : 0;

    $.ajax({
        url: url,
        method: 'post',
        data: {
            id,
            model,
            status,
            _token: csrf
        },
        success: function (response) {
            toastr_alert(response.status, response.message, response.status);
        }
    });
}

function deleteUser(id, url) {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to delete this user?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'No, Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    id: id,
                    _token: csrf // ensure csrf variable is defined
                },
                success: function(response) {
                    Swal.fire(
                        'Deleted!',
                        response.message,
                        'success'
                    );
                    $('.datatable-ajax').DataTable().ajax.reload();
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'Something went wrong',
                        'error'
                    );
                }
            });
        }
    });
}
function applySpaceRules(selector, mode = 'noSpace') {
    if (mode === 'noSpace') {
        // No spaces at all
        $(document).on('keydown', selector, function (e) {
            if (e.key === ' ') {
                e.preventDefault();
            }
        });
    }

    if (mode === 'noLeadingSpace') {
        // Allow spaces inside, but not at start or double spaces
        $(document).on('input', selector, function () {
            if (this.type === 'file') return;
            // Remove leading spaces
            let val = this.value.replace(/^\s+/, '');

            // Replace multiple consecutive spaces with a single space
            val = val.replace(/\s{2,}/g, ' ');
            val = val.toLowerCase().replace(/(^\w|\s\w)/g, m => m.toUpperCase());

            this.value = val;
        });
    }
}

function applySingleSpaceSingleBreak(selector) {

    $(document).on('input', selector, function () {

        let val = this.value;

        // Remove leading spaces
        val = val.replace(/^\s+/, '');

        // Replace multiple spaces with single space
        val = val.replace(/ {2,}/g, ' ');

        // Replace multiple line breaks with single line break
        val = val.replace(/\n{2,}/g, '\n');

        // Remove space at start of each new line
        val = val.replace(/\n\s+/g, '\n');

        this.value = val;

    });

}

function allowPhoneWithPlusAndSpace(selector) {
    $(document).on('input', selector, function () {
        let val = $(this).val();

        val = val.replace(/(?!^\+)[^0-9\s]/g, '');

        $(this).val(val);
    });
}

function allowNumericOnly(selector) {
    $(document).on('input', selector, function () {
        // Remove any non-digit characters
        this.value = this.value.replace(/\D/g, '');
    });

    // Optional: prevent pasting non-numeric
    $(document).on('paste', selector, function (e) {
        const pasteData = (e.originalEvent || e).clipboardData.getData('text');
        if (/\D/.test(pasteData)) e.preventDefault();
    });
}

function limitrInput(selector) {
    $(document).on('input', selector, function () {
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
    });
}

function allowAlphaNumericOnly(selector) {
    $(document).on('input', selector, function () {
        // Allow letters, numbers, and spaces
        this.value = (this.value || '').replace(/[^a-zA-Z0-9 ]/g, '');
    });

    // Optional: prevent pasting invalid characters
    $(document).on('paste', selector, function (e) {
        const pasteData = (e.originalEvent || e).clipboardData.getData('text');
        if (/[^a-zA-Z0-9 ]/.test(pasteData)) e.preventDefault();
    });
}

