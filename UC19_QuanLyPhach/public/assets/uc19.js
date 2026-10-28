document.querySelectorAll('[data-filter]').forEach((select, index, selects) => {
    select.addEventListener('change', () => {
        // Bỏ giá trị con khi đổi cấp cha để server chọn lại phạm vi hợp lệ.
        selects.forEach((child, childIndex) => { if (childIndex > index) child.disabled = true; });
        select.form.requestSubmit();
    });
});
