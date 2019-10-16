$(document).ready(function () {
    $('i.collapse-link-li').on('click', function () {
        var $BOX_PANEL = $(this).closest('li'),
            $ICON = $(this),
            $BOX_CONTENT = $BOX_PANEL.children('ul');

        // fix for some div with hardcoded fix class
        if ($BOX_PANEL.attr('style')) {
            $BOX_CONTENT.slideToggle(200, function () {
                $BOX_PANEL.removeAttr('style');
            });
        } else {
            $BOX_CONTENT.slideToggle(200);
            $BOX_PANEL.css('height', 'auto');
        }

        $ICON.toggleClass('fa-chevron-up fa-chevron-down');
    });

    $('#paymentPoint').on('change', function () {
        //console.log($('.js-url').attr('required', 'required'));
        if ($(this).val() == 'offline') {
            $('.js-address').attr('required', 'required').fadeIn(300);
            $('.js-url').hide().find('input').removeAttr('required');
            $('.js-url').find('input').val('');
        } else if ($(this).val() == 'online') {
            $('.js-address').hide().find('input').removeAttr('required');
            $('.js-url').attr('required', 'required').fadeIn(300);
            $('.js-address').find('input').val('');
        }
    }).trigger('change');

    $('#companyOrgType').on('change', function () {
        if ($(this).val() == 1) {
            $('.js-company-type-ip').attr('required', 'required').fadeIn(300);
            $('.js-company-type-ooo').hide().find('input').removeAttr('required');
            $('.js-company-type-ooo').find('input').val('');
        } else {
            $('.js-company-type-ip').hide().find('input').removeAttr('required');
            $('.js-company-type-ooo').attr('required', 'required').fadeIn(300);
            $('.js-company-type-ip').find('input').val('');
        }
    }).trigger('change');

    $('.js-add-kkt').on('click', function () {
        $(this).hide();
        $('.js-new-kkt').fadeIn(300);
    });
    $('.js-add-kkt-cancel').on('click', function () {
        $('.js-new-kkt').hide();
        $('.js-add-kkt').fadeIn(300);
    });

    $('#peak, #transaction').on('input', calcKKT);

    function calcKKT() {
        var kktp = Math.ceil($('#peak').val() / 2);  //ob.peak = Пик транзакций в секунду
        var kktv = Math.ceil(($('#transaction').val() / 12) / 23000);  //ob.transaction = Транзакций в год
        $('#recomendedKKT').val(Math.ceil(Math.max(kktv, kktp)));	// получить большее значение и округлить
    }

    //Показ сообщений
    function showMessages(_json) {
        if (_json.messages) {
            for (var type in _json.messages) {
                var messages = _json.messages[type];
                for (var i in messages) {
                    new PNotify({
                        title: 'Сообщение сайта',
                        text: messages[i],
                        styling: 'bootstrap3',
                        type: type
                    });
                }
            }
        }
    }

    $('.js-confirm-invoice').on('click', function () {
        if (confirm('Вы уверены?')) {
            // window.header.href = $(this).data('url');
            $(location).attr('href', $(this).data('url'))
        }
    });

    //предлагаю все потверждения делать именно так
    $('#confirmModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var id = button.data('id');
        $(this).find('input[name="id"]').val(id);
    });

    $('.js-ofd-remove').on('click', function () {
        if (confirm('Вы уверены, что хотите удалить?')) {
            $.ajax({
                url: $(this).closest('form').attr('action'),
                method: 'DELETE',
                success: function (_json) {
                    $(location).attr('href', _json.url);
                },
                error: function () {
                    alert('Ошибка сервиса');
                }
            })
        }
    });

    $('.js-company-remove').on('click', function () {
        if (confirm('Вы уверены, что хотите удалить?')) {
            var ele = $(this);
            $.ajax({
                url: $(ele).data('url'),
                method: 'DELETE',
                success: function (_json) {
                    $(ele).closest('tr').remove();
                },
                error: function () {
                    alert('Ошибка сервиса');
                }
            })
        }
    });
    $('.js-invoice-remove').on('click', function () {
        if (confirm('Вы уверены, что хотите удалить?')) {
            var ele = $(this);
            $.ajax({
                url: $(ele).data('url'),
                method: 'DELETE',
                success: function (_json) {
                    $(location).attr('href', _json.url);
                },
                error: function () {
                    alert('Ошибка сервиса');
                }
            })
        }
    });
    $('.js-invoice-item-remove').on('click', function () {
        if (confirm('Вы уверены, что хотите удалить?')) {
            var ele = $(this);
            $.ajax({
                url: $(ele).data('url'),
                method: 'DELETE',
                success: function (_json) {
                    if (_json.url) {
                        $(location).attr('href', _json.url);
                        enh
                    } else {
                        $(ele).closest('tr').remove();
                    }
                },
                error: function () {
                    alert('Ошибка сервиса');
                }
            })
        }
    });

    $('.js-office-get-kkt').on('click', function () {
        var ele = $(this);
        var table = $(ele).closest('table');
        $(this).addClass('disabled');
        $(ele).find('.glyphicon').hide();
        $(ele).find('.fa').fadeIn(300);

        $.ajax({
            url: $(this).data('url'),
            data: {company: $(table).data('company'), shop: $(table).data('shop')},
            method: 'POST',
            success: function (_json) {
                showMessages(_json);
                $(ele).find('.glyphicon').fadeIn(300);
                $(ele).find('.fa').hide(300);
                if (_json.success) {
                    var td = $(ele).closest('td');
                    // $(td).find('.js-office-kkt-info').show();
                    // $(td).find('.js-office-kkt-remove').show();
                    $(td).find('.js-office-kkt-rnm').show();
                    $(td).find('.js-office-kkt-doc').show();
                    $(ele).hide();
                    var tr = $(ele).closest('tr');
                    $(tr).find('.js-kkt-status-label').removeClass('label-warning').addClass('label-primary').text('Установлена');
                    $(tr).find('.js-company-balance').text(_json.balance + ' руб.');
                    $(tr).find('.js-office-kkt-number').text(_json.kkt.serilaNumber);
                    $(tr).find('.js-office-fn').text(_json.kkt.fsNumber);
                    $(tr).find('.js-office-fn-version').text(_json.kkt.fsVersion);
                } else {
                    $(ele).removeClass('disabled');
                }
            },
            error: function () {
                $(ele).removeClass('disabled');
                new PNotify({
                    title: 'Сообщение сайта',
                    text: 'Ошибка сервиса, попробуйте позже',
                    styling: 'bootstrap3',
                    type: 'danger'
                });
                $(ele).find('.glyphicon').fadeIn(300);
                $(ele).find('.fa').hide(300);
            }
        });
    });

    $('.js-office-kkt-remove').on('click', function () {
        if ($(this).data('send-fn')) {
            $('.js-office-send-fn').show();
        } else {
            $('.js-office-send-fn').hide();
        }
        $('.js-office-kkt-remove-confirm').attr('data-url', $(this).data('url'));
    });

    $('.js-office-kkt-remove-confirm').on('click', function () {
        var ele = $('.js-office-kkt-remove[data-url=\'' + $(this).data('url') + '\']');
        var table = $(ele).closest('table');
        $(ele).addClass('disabled');
        // $(ele).find('.glyphicon').hide();
        // $(ele).find('.fa').fadeIn(300);
        $('#myModal').modal('hide');

        $.ajax({
            url: $(this).data('url'),
            data: {
                company: $(table).data('company'),
                shop: $(table).data('shop'),
                isSend: $('[name="sendKkt"]').prop('checked')
            },
            method: 'DELETE',
            success: function (_json) {
                showMessages(_json);
                $(ele).find('.glyphicon').fadeIn(300);
                $(ele).find('.fa').hide(300);
                if (_json.success) {
                    var td = $(ele).closest('td');
                    $(td).find('.js-office-kkt-refresh').hide();
                    $(td).find('.js-office-kkt-info').hide();
                    $(td).find('.js-office-get-kkt').hide();
                    $(td).find('.js-office-kkt-rnm').hide();
                    $(ele).hide();
                    $(ele).closest('tr').find('.js-kkt-status-label').removeClass('label-success, label-primary').addClass('label-warning').text('Не установлена');
                }
                $(ele).removeClass('disabled');
            },
            error: function () {
                $(ele).removeClass('disabled');
                new PNotify({
                    title: 'Сообщение сайта',
                    text: 'Ошибка сервиса, попробуйте позже',
                    styling: 'bootstrap3',
                    type: 'danger'
                });
                $(ele).find('.glyphicon').fadeIn(300);
                $(ele).find('.fa').hide(300);
            }
        });
    });

    $('.js-office-kkt-rnm').on('click', function () {
        $('.js-office-kkt-rnm-set').attr('data-url', $(this).data('url'));
    });
    $('.js-office-kkt-rnm-set').on('click', function () {
        var val = $('[name="rnm"]').val();
        if (val.length == 0) {
            new PNotify({
                title: 'Сообщение сайта',
                text: 'Не заполнено поле РНМ',
                styling: 'bootstrap3',
                type: 'danger'
            });
            return;
        }
        $('#setRnmModal').modal('hide');
        var ele = $('.js-office-kkt-rnm[data-url=\'' + $(this).data('url') + '\']');

        $.ajax({
            url: $(this).data('url'),
            method: 'POST',
            data: {rnm: val},
            success: function (_json) {
                showMessages(_json);
                $(ele).closest('tr').find('.js-rnm').text(val);
            },
            error: function () {

            }
        });
    });

    $('.js-office-kkt-info').on('click', function () {
        $.ajax({
            url: $(this).data('url'),
            method: 'GET',
            success: function (_json) {

            },
            error: function (_json) {

            }
        });
    });

    $('.js-get-access').on('click', function () {
        $.ajax({
            url: $(this).data('url'),
            method: 'GET',
            success: function (_json) {
                showMessages(_json);
                $('.js-show-access').text('Логин: ' + _json.login + ', пароль: ' + _json.password);
            },
            error: function () {

            }
        });
    });

    $('.js-admin-kkt-edit').on('click', function () {
        $('#myModal').modal('hide');
        $('.js-kkt-edit-from').submit();
    });
});