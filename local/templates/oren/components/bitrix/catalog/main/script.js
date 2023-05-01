
console.log('go');
$(document).on('click', '[data-catalog-sort] input', function(e) {
    window.location.href = BX.util.add_url_param(window.location.href, {'sort': this.value});
});