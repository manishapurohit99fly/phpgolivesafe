let sectionIndex = 0;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function requiredMark(field) {
    return field.required
        ? '<span class="text-danger">*</span>'
        : '';
}

function requiredAttr(field) {
    return field.required
        ? 'required'
        : '';
}

function fieldName(index, key) {
    return `sections[${index}][content][${key}]`;
}

function escapeAttribute(str = '') {
    const input = document.createElement('input');
    input.setAttribute('value', str);
    return input.outerHTML.match(/value="(.*)"/)[1];
}

/*
|--------------------------------------------------------------------------
| Shared File Renderer
|--------------------------------------------------------------------------
*/

function fileRenderer(field, index, accept = '*') {

    return `
        <div class="col">
            <div class="mb-3">
                <label class="form-label"> ${field.label} ${requiredMark(field)} </label>
                ${
                    field.value
                    ? `
                        <div class="mb-2">
                            <a href="${field.value}"
                                target="_blank"
                                class="text-primary">
                                View Current File
                            </a>
                        </div>
                    `
                    : ''
                }

                <input type="file"
                    class="form-control"
                    name="${fieldName(index, field.key)}"
                    accept="${accept}"
                    ${requiredAttr(field)}>

            </div>

        </div>
    `;
}

/*
|--------------------------------------------------------------------------
| Repeater Renderer
|--------------------------------------------------------------------------
*/

function renderRepeater(field, index) {

    return `
        <div class="col-12">
            <div class="mb-4 repeater-wrapper" data-field-key="${field.key}" data-section-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">
                        ${field.label}
                    </label>
                    <button type="button" class="btn btn-primary btn-sm addRepeaterRow">
                        Add Row
                    </button>
                </div>
                <div class="repeaterRows row row-cols-2 g-3"></div>
            </div>
        </div>
    `;
}

/*
|--------------------------------------------------------------------------
| Field Renderers
|--------------------------------------------------------------------------
*/

const fieldRenderers = {
    text(field, index) {
        return `
            <div class="col">
                <div class="mb-3">
                    <label class="form-label">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>
                    <input type="text" class="form-control" name="${fieldName(index, field.key)}" value="${field.default ?? ''}" ${requiredAttr(field)}>
                </div>
            </div>
        `;
    },

    textarea(field, index) {

        return `
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>
                    <textarea class="form-control" rows="4" name="${fieldName(index, field.key)}" ${requiredAttr(field)}>${field.default ?? ''}</textarea>
                </div>

            </div>
        `;
    },

    number(field, index) {

        return `
            <div class="col">
                <div class="mb-3">
                    <label class="form-label">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>
                    <input type="number" class="form-control" name="${fieldName(index, field.key)}" value="${field.default ?? ''}" ${requiredAttr(field)}>
                </div>

            </div>
        `;
    },

    email(field, index) {

        return `
            <div class="col">
                <div class="mb-3">
                    <label class="form-label">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>
                    <input type="email" class="form-control" name="${fieldName(index, field.key)}" value="${field.default ?? ''}" ${requiredAttr(field)}>
                </div>
            </div>
        `;
    },

    link(field, index) {

        return `
            <div class="col">
                <div class="mb-3">
                    <label class="form-label">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>
                    <input type="url"
                        class="form-control"
                        placeholder="https://example.com"
                        name="${fieldName(index, field.key)}"
                        value="${field.default ?? ''}"
                        ${requiredAttr(field)}>
                </div>
            </div>
        `;
    },

    select(field, index) {

        let options = '';

        field.options?.forEach(option => {

            options += `
                <option value="${option.value}"
                    ${field.default == option.value ? 'selected' : ''}>
                    ${option.label}
                </option>
            `;
        });

        return `
            <div class="col">
                <div class="mb-3">
                    <label class="form-label">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>
                    <select class="form-select"  name="${fieldName(index, field.key)}"  ${requiredAttr(field)}>
                        <option value="">
                            Select Option
                        </option>
                        ${options}
                    </select>
                </div>
            </div>
        `;
    },

    radio(field, index) {

        let radios = '';

        field.options?.forEach((option, i) => {

            radios += `
                <div class="form-check">

                    <input class="form-check-input"
                        type="radio"
                        id="${field.key}_${i}_${index}"
                        name="${fieldName(index, field.key)}"
                        value="${option.value}"
                        ${field.default == option.value ? 'checked' : ''}>

                    <label class="form-check-label"
                        for="${field.key}_${i}_${index}">
                        ${option.label}
                    </label>

                </div>
            `;
        });

        return `
            <div class="col">

                <div class="mb-3">

                    <label class="form-label d-block">
                        ${field.label}
                        ${requiredMark(field)}
                    </label>

                    ${radios}

                </div>

            </div>
        `;
    },

    checkbox(field, index) {

        let checkboxes = '';

        field.options?.forEach((option, i) => {

            checkboxes += `
                <div class="form-check">

                    <input class="form-check-input"
                        type="checkbox"
                        id="${field.key}_${i}_${index}"
                        name="${fieldName(index, field.key)}[]"
                        value="${option.value}">

                    <label class="form-check-label"
                        for="${field.key}_${i}_${index}">
                        ${option.label}
                    </label>

                </div>
            `;
        });

        return `
            <div class="col">
                <div class="mb-3">
                    <label class="form-label d-block">${field.label} ${requiredMark(field)}</label>
                    ${checkboxes}
                </div>
            </div>
        `;
    },

    image(field, index) {
        return fileRenderer(field, index, 'image/*');
    },

    file(field, index) {
        return fileRenderer(
            field,
            index,
            '.pdf,.doc,.docx,.xls,.xlsx,.txt'
        );
    },

    video(field, index) {
        return fileRenderer(field, index, 'video/*');
    },

    repeater(field, index) {
        return renderRepeater(field, index);
    }

};

/*
|--------------------------------------------------------------------------
| Add Section
|--------------------------------------------------------------------------
*/

$('#addSectionBtn').on('click', function () {
    renderSectionCard();
    refreshSectionIndexes();
});

/*
|--------------------------------------------------------------------------
| Render Section Card
|--------------------------------------------------------------------------
*/

function renderSectionCard(sectionData = null)
{
    let options = '';
    Object.entries(window.sectionsConfig).forEach(([key, section]) => {
        options += `
            <option value="${key}">
                ${section.label}
            </option>
        `;
    });
    const html = `
        <div class="card mb-4 shadow-sm section-card">

            <input type="hidden"
                class="sorting-order"
                name="sections[${sectionIndex}][sort_order]"
                value="${sectionIndex + 1}">

            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <strong>Section</strong>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" class="toggle-sec p-2 d-flex gap-2 align-items-center text-decoration-none">Show
                        <i class="fa-solid fa-angle-down"></i>
                    </a>
                    <button type="button" class="btn btn-danger btn-sm remove-section">  Remove </button>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-3 pb-3">
                    <label class="form-label">Section Type</label>
                    <select class="form-select section-type" name="sections[${sectionIndex}][key]" data-index="${sectionIndex}">
                        <option value="">  Select Section </option>
                        ${options}
                    </select>
                </div>
                <div class="dynamic-fields row row-cols-2 g-3"></div>
            </div>
        </div>
    `;

    $('#sectionsWrapper').append(html);

    sectionIndex++;
}

/*
|--------------------------------------------------------------------------
| Section Type Change
|--------------------------------------------------------------------------
*/

$(document).on('change', '.section-type', function () {

    const sectionKey = $(this).val();

    const index = $(this).data('index');

    const fieldsWrapper = $(this)
        .closest('.card-body')
        .find('.dynamic-fields');

    fieldsWrapper.html('');

    if (!sectionKey) {
        return;
    }

    const section = window.sectionsConfig[sectionKey];

    section.fields.forEach(field => {

        const renderer = fieldRenderers[field.type];

        if (renderer) {

            fieldsWrapper.append(
                renderer(field, index)
            );

        } else {

            console.warn(
                `Renderer not found for type: ${field.type}`
            );

        }

    });

});

/*
|--------------------------------------------------------------------------
| Remove Section
|--------------------------------------------------------------------------
*/

$(document).on('click', '.remove-section', function () {

    $(this)
        .closest('.section-card')
        .remove();

    refreshSectionIndexes();

});

/*
|--------------------------------------------------------------------------
| Add Repeater Row
|--------------------------------------------------------------------------
*/

$(document).on('click', '.addRepeaterRow', function () {

    let wrapper = $(this).closest('.repeater-wrapper');

    let sectionIndex = wrapper.data('section-index');

    let fieldKey = wrapper.data('field-key');

    let sectionCard = wrapper.closest('.section-card');

    let sectionType = sectionCard.find('.section-type').val();

    let sectionConfig = window.sectionsConfig[sectionType];

    let repeaterField = sectionConfig.fields.find(
        f => f.key === fieldKey
    );

    let rowIndex = `row_${Date.now()}`;

    let rowFields = '';

    repeaterField.fields.forEach(subField => {

        const subFieldName =
            `sections[${sectionIndex}][content][${fieldKey}][${rowIndex}][${subField.key}]`;

        if (subField.type === 'textarea') {

            rowFields += `
                <div class="col-12">

                    <div class="mb-3">

                        <label class="form-label">
                            ${subField.label}
                            ${requiredMark(subField)}
                        </label>

                        <textarea
                            class="form-control"
                            rows="3"
                            name="${subFieldName}"
                            ${requiredAttr(subField)}></textarea>

                    </div>

                </div>
            `;

        } else if (
            subField.type === 'image'
            || subField.type === 'file'
            || subField.type === 'video'
        ) {

            let accept = '*';

            if (subField.type === 'image') {
                accept = 'image/*';
            }

            if (subField.type === 'video') {
                accept = 'video/*';
            }

            if (subField.type === 'file') {
                accept = '.pdf,.doc,.docx,.xls,.xlsx,.txt';
            }

            rowFields += `
                <div class="col">

                    <div class="mb-3">

                        <label class="form-label">
                            ${subField.label}
                            ${requiredMark(subField)}
                        </label>

                        <input type="file"
                            class="form-control"
                            name="${subFieldName}"
                            accept="${accept}"
                            ${requiredAttr(subField)}>

                    </div>

                </div>
            `;

        } else {

            rowFields += `
                <div class="col">
                    <div class="mb-3">
                        <label class="form-label">
                            ${subField.label}
                            ${requiredMark(subField)}
                        </label>

                        <input type="text"
                            class="form-control"
                            name="${subFieldName}"
                            ${requiredAttr(subField)}>

                    </div>

                </div>
            `;
        }

    });

    let rowHtml = `
        <div class="col">

            <div class="repeater-row card h-100 p-3">

                <div class="row row-cols-2 ">
                    ${rowFields}
                </div>

                <div class="text-end">

                    <button type="button"
                        class="btn btn-danger btn-sm removeRepeaterRow">

                        Remove

                    </button>

                </div>

            </div>

        </div>
    `;

    wrapper.find('.repeaterRows')
        .append(rowHtml);

});

/*
|--------------------------------------------------------------------------
| Remove Repeater Row
|--------------------------------------------------------------------------
*/

$(document).on('click', '.removeRepeaterRow', function () {

    $(this)
        .closest('.col')
        .remove();

});

/*
|--------------------------------------------------------------------------
| Show / Hide Sections
|--------------------------------------------------------------------------
*/

$(document).on('click', '.toggle-sec', function (e) {

    e.preventDefault();
    let sectionCard = $(this).closest('.section-card');
    let fieldsWrapper = sectionCard.find('.dynamic-fields');
    fieldsWrapper.slideToggle(200, function () {
        let isVisible = $(this).is(':visible');
        $(this).find(':input[required]').prop('required', isVisible);

    });

    if ($(this).html().includes('Show')) {

        $(this).html(`
            Hide
            <i class="fa-solid fa-angle-up"></i>
        `);

    } else {

        $(this).html(`
            Show
            <i class="fa-solid fa-angle-down"></i>
        `);
    }

});

/*
|--------------------------------------------------------------------------
| Sortable
|--------------------------------------------------------------------------
*/

$(function () {
    $("#sectionsWrapper").sortable({
        handle: '.card-header',
        update: function () {
            refreshSectionIndexes();
        }

    });
    $("#sectionsWrapper").disableSelection();
});

/*
|--------------------------------------------------------------------------
| Refresh Indexes
|--------------------------------------------------------------------------
*/

$('#submitBtn').on('click', function (e) {
    e.preventDefault();
    const form = $(this).closest('form');
    refreshSectionIndexes();
    setTimeout(() => {
        form.submit();
    }, 300);
});

function refreshSectionIndexes()
{
    $('#sectionsWrapper .section-card').each(function(index) {

        $(this).find('.sorting-order').val(index + 1);
        $(this).find('[name]').each(function() {
            let name = $(this).attr('name');
            if (name) {
                name = name.replace(
                    /sections\[\d+\]/g,
                    `sections[${index}]`
                );
                $(this).attr('name', name);

            }

        });
        $(this).find('.section-type').attr('data-index', index);
    });

    sectionIndex = $('#sectionsWrapper .section-card').length;
}