
    $(function() {

        function validateProfilePhotoFile(file) {
            if (!file) return true;
            const validTypes = ["image/jpeg", "image/png", "image/webp"];
            if ($.inArray(file.type, validTypes) < 0) {
                $(".error-profile_photo").text("Only JPG, JPEG, PNG, and WebP files are allowed.");
                return false;
            }
            const maxBytes = 2 * 1024 * 1024; // 2 MB
            if (file.size > maxBytes) {
                $(".error-profile_photo").text("Image must be 2 MB or smaller.");
                return false;
            }
            $(".error-profile_photo").text("");
            return true;
        }

        // Validate after cropper confirms the file (cropper dispatches 'cropped')
        $("#profile_photo").on("cropped", function () {
            const file = this.files[0];
            if (!validateProfilePhotoFile(file)) {
                $(this).val("");
            }
        });

        // Ajax form submit
        $("#profileForm").on("submit", function(e) {
            e.preventDefault();

            let val = cleanSpaces($("#first-name").val());
            let regex = /^[A-Za-z ]*$/;

            $(".error-name").text(""); // clear old error

            if (!regex.test(val) || val.length === 0) {
                $(".error-name").text("Please enter a valid name.");
                return false; 
            }

            let formData = new FormData(this);
            $(".text-danger").text(""); 
            $("#responseMessage").html("");

            $.ajax({
                url: $(this).attr("action") ,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        toastr_alert(response.status, response.message, 'success');
                        // Clear persisted cropped image so the next page load starts fresh
                        if (typeof window.cropperClearStored === 'function') {
                            window.cropperClearStored(document.getElementById('profile_photo'));
                        }
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        toastr_alert(response.status, response.message, 'error');
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            $(".error-" + key).text(value[0]);
                        });
                    } else {
                        toastr_alert(xhr.status, 'Something went wrong', xhr.status);
                    }
                }
            });
        });


        function cleanSpaces(str) {
            return str.replace(/\s+/g, " ").trim(); // multiple spaces → single, trim
        }

        $("#first-name").on("input blur", function () {
        let val = $(this).val();


        // 2. Only letters + space allowed (change regex if you want letters+numbers)
        let regex = /^[A-Za-z ]*$/; // only letters and spaces
        if (!regex.test(val)) {
            $(".error-name").text("Please enter a valid name.");
        } else {
            $(".error-name").text("");
        }

        // 3. Capitalization (Sentence case)

        // 4. Update input value
        $(this).val(val);
        });

        // Optional: prevent form submit if invalid
        $("#profileForm").on("submit", function (e) {
            let val = cleanSpaces($("#first-name").val());
            let regex = /^[A-Za-z ]*$/;
            if (!regex.test(val) || val.length === 0) {
                e.preventDefault();
                $(".error-name").text("Please enter a valid name.");
            }
        });

    });
