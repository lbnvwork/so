(function ($) {

    var ob = {};


    function countOnline() {

        var kktp = Math.ceil(ob.peak / 2);
        var kktv = Math.ceil((ob.transaction / 12) / 23000);
        var result = Math.ceil(Math.max(kktv, kktp));
        return result;
    }


    /*function countCostMonth(kktq) {
        return parseInt((ob.cenaKKM * kktq) + (ob.cenaFN * kktq));
    }*/

    function makeCorrectNumberFormat(num) {
        num = "" + num;
        if (num.length < 4) {
            return num;
        }
        return num.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
    }


    function fisIF(twoNum, oneNum) {
        if (twoNum === '11' || twoNum === '12' || twoNum === '13' || twoNum === '14') {
            return ['ыx', 'лей'];
        } else {
            if (oneNum === '1') {
                return ['ый', 'ль'];
            } else if (oneNum === '2' || oneNum === '3' || oneNum === '4') {
                return ['ыx', 'ля'];
            } else {
                return ['ыx', 'лей'];
            }
        }
    }


    function run() {

        ob.summab15 = $('#summa15');
        ob.summab36 = $('#summa36');
        ob.fiscal1 = $('#fiskal1');
        ob.fiscal2 = $('#fiskal2');
        ob.mountb = $('#mount');
        ob.yearb = $('#year');
        ob.intransactionb = $('#intransaction');
        ob.transactionb = $('#transaction');
        ob.inhighpointb = $('#inhighpoint');
        ob.highpointb = $('#highpoint');
        ob.suffix0 = $('#suffix0');
        ob.suffix1 = $('#suffix1');

        ob.summa = 100;
        ob.mount = 100;
        ob.year = 10000;
        ob.transaction = 0;
        ob.peak = 0;

        ob.cenaKKM = 1800;
        ob.cenaFN15 = 6900;
        ob.cenaFN36 = 9990;

        ob.timeout = null;


        //ob.intransactionb.on('focus', function() { ob.intransactionb.val(''); });
        ob.intransactionb.on('blur', function () {
            ob.intransactionb.val(ob.transaction);
        });
        ob.intransactionb.on('keyup', {in1: ob.intransactionb, in2: ob.transactionb, t: 'transaction'}, eventFunction);
        ob.transactionb.on('input', {in1: ob.transactionb, in2: ob.intransactionb, t: 'transaction'}, eventFunction);

        //ob.inhighpointb.on('focus', function() { ob.inhighpointb.val(''); });
        ob.inhighpointb.on('blur', function () {
            ob.inhighpointb.val(ob.peak);
        });
        ob.inhighpointb.on('keyup', {in1: ob.inhighpointb, in2: ob.highpointb, t: 'peak'}, eventFunction);
        ob.highpointb.on('input', {in1: ob.highpointb, in2: ob.inhighpointb, t: 'peak'}, eventFunction);

    }

    function eventFunction(e) {
        var in1 = e.data.in1;
        var in2 = e.data.in2;

        var v = parseInt(in1.val());
        if (isNaN(v)) {
            v = 0;
        }

        in2.val(v);
        in1.val(v);
        ob[e.data.t] = v;
        if (v > 0) {
            clearTimeout(ob.timeout);
            ob.timeout = setTimeout(innerResult, 500);
        }
    }

    function innerResult() {
        var kkm = countOnline();
        ob.fiscal1.html(kkm);
        ob.fiscal2.html(kkm);

        var mount = kkm * ob.cenaKKM;//countCostMonth(kkm, ob.transaction);
        var year = (mount * 12).toFixed(0);

        ob.yearb.html(makeCorrectNumberFormat(year));
        ob.mountb.html(makeCorrectNumberFormat(mount));
        ob.summab15.html(makeCorrectNumberFormat(kkm * ob.cenaFN15));
        ob.summab15.html();
        ob.summab36.html(makeCorrectNumberFormat(kkm * ob.cenaFN36));
        ob.summab36.html();

        var kkmstr = kkm + '';
        if (kkmstr.length >= 3) {
            var twoNum = kkmstr.slice(-2);
            var oneNum = kkmstr.slice(-1);
        } else if (kkmstr.length < 3) {
            var twoNum = kkmstr;
            var oneNum = kkmstr.slice(-1);
        }
        var suf = fisIF(twoNum, oneNum);
        ob.suffix0.html(suf[0]);
        ob.suffix1.html(suf[1]);


        //var suf = fisIF(twoNum, oneNum);

    }


    $(document).ready(function () {
        new WOW().init();
        if (typeof $.mask != 'undefined') {
        } else if (typeof console.warn != 'undefined') {
            console.warn('Conflict when accessing the jQuery Mask Input Plugin: %s typeof $.mask', typeof $.mask);
        }

        // new WOW().init();
        if (typeof $.dropdown != 'undefined') {
            $(".afbf_item_pole .afbf_select").dropdown({
                "dropdownClass": "feedback_dropdown"
            });
        }

        var file_w_FID1 = parseInt($("#alx_feed_back_FID1 .afbf_feedback_poles").width() / 5);

        function str_replace_FID1(search, replace, subject) {
            return subject.split(search).join(replace);
        }


        $('.eye').click(function () {
            var type = $('#password').attr('type') == "text" ? "password" : 'text';
            var title = $('.eye').attr('title') == "Показать пароль" ? "Скрыть пароль" : 'Показать пароль';
            if ($(this).hasClass('show')) {
                $(this).removeClass("show");
                $(this).addClass("hide");
            } else {
                $(this).removeClass("hide");
                $(this).addClass("show");
            }
            $('#password').prop('type', type);
            $('.eye').prop('title', title);
        });

        run();
        $('.toggle-dropdown').on('click', function () {
            $(this).next('.scheme').toggleClass('is-active');
            $(this).children('.down-icon').toggleClass('is-active');
        });

        $('.callback').on('click', function () {
            $('.callbackkiller').trigger('click');
        });

        function trans() {
            var transaction = document.getElementById('transaction');
            var intransaction = document.getElementById('intransaction');
            intransaction.value = transaction.value;
        }

        function high() {
            var highpoint = document.getElementById('highpoint');
            var inhighpoint = document.getElementById('inhighpoint');
            inhighpoint.value = highpoint.value;
        }

        $('.owl-carousel').owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            dots: false,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 3
                },
                1000: {
                    items: 5
                }
            }
        });

        $('#needCms').on('submit', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');

            $.ajax($(form).attr('action'), {
                method: 'POST',
                data: $(form).serialize(),
                success: function (_json) {
                    if (_json.success) {
                        $(form).find('.captchaError').hide();
                        $('.js-success-cms').show();
                        $('.js-cms-form').hide();
                        ym(48966332, 'reachGoal', 'CMS');
                    } else {
                        $(form).find('.captchaError').show();
                    }
                },
                error: function (_json) {
                    console.log(_json);
                }
            });
        });
        $('#needPromo').on('submit', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');

            $.ajax($(form).attr('action'), {
                method: 'POST',
                data: $(form).serialize(),
                success: function (_json) {
                    $('#promoSuccess').show();
                    $('#promoForm').hide();
                    ym(48966332, 'reachGoal', 'PROMO');
                    ym(48966332, 'reachGoal', 'REGISTRATION');
                },
                error: function (_json) {
                    console.log(_json);
                }
            });
        });
        $('.js-register').on('submit', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');

            $.ajax($(form).attr('action'), {
                method: 'POST',
                data: $(form).serialize(),
                success: function (_json) {
                    $(form).closest('.js-connect-parent').find('.js-register-form').hide();
                    $(form).closest('.js-connect-parent').find('.js-success-register').show();
                    $(form).closest('.js-connect-parent').find('.js-success-register .mess').append(_json.msg);
                    if (_json.success) {
                        $(form).closest('.js-connect-parent').find('.js-success-register .afbf_icon').addClass('afbf_ok_icon');
                        ym(48966332, 'reachGoal', 'REGISTRATION');
                    } else {
                        $(form).closest('.js-connect-parent').find('.js-success-register .afbf_icon').addClass('afbf_error_icon');
                    }
                },
                error: function (_json) {
                    console.log(_json);
                }
            });
        });
    });
    $('.js-tariff-button').on('click', function (e) {
        Cookies.set('tariffId', $(this).attr('id'));
    });

    $('.accordion a').click(function(j) {
        var dropDown = $(this).closest('li').find('p');
        $(this).closest('.accordion').find('p').not(dropDown).slideUp();
        if ($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).closest('.accordion').find('a.active').removeClass('active');
            $(this).addClass('active');
        }
        dropDown.stop(false, true).slideToggle();
        j.preventDefault();
    });
})(jQuery);