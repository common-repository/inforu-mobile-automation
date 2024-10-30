window.addEventListener('DOMContentLoaded', function () {
    (function ($) {
        if (typeof wcInforuConfig === 'undefined') {
            return false;
        }
        const cronUrl = wcInforuConfig.cronUrl || '';
        if (!cronUrl) {
            return false;
        }
        $.get(cronUrl, {'_': new Date().getTime()});
    })(jQuery);
});