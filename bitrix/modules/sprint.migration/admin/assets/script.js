function migrationMigrationsUpConfirm() {
    if (confirm('Confirm install migrations')) {
        migrationExecuteStep('migration_execute', {
            'next_action': 'up'
        });
    }
}

function migrationMigrationsDownConfirm() {
    if (confirm('Confirm rollback migrations')) {
        migrationExecuteStep('migration_execute', {
            'next_action': 'down'
        });
    }
}

function migrationMigrationsUpWithTag() {
    var settag = prompt('Set migrations tag');
    if (settag !== null) {
        migrationExecuteStep('migration_execute', {
            'next_action': 'up',
            'settag': settag
        });
    }
}

function migrationMigrationUp(version) {
    migrationExecuteStep('migration_execute', {
        'version': version,
        'action': 'up'
    });
}

function migrationMigrationDown(version) {
    migrationExecuteStep('migration_execute', {
        'version': version,
        'action': 'down'
    });
}

function migrationMigrationSetTag(version, defaultTag) {
    var settag = prompt('Set migration tag', defaultTag);
    if (settag !== null) {
        migrationExecuteStep('migration_settag', {
            'version': version,
            'settag': settag
        });
    }
}

function migrationMigrationMark(version, status) {
    migrationExecuteStep('migration_mark', {
        'version': version,
        'status': status,
    });
}

function migrationMigrationTransfer(version, transferTo) {
    migrationExecuteStep('migration_transfer', {
        'version': version,
        'transfer_to': transferTo,
    });
}

function migrationMigrationDelete(version) {
    migrationExecuteStep('migration_delete', {
        'version': version,
    });
}

function migrationOutLog(result) {
    var $el = $('#migration_progress');
    var lastOutElem = $el.children('div').last();
    if (lastOutElem.hasClass('sp-progress') && $(result).first().hasClass('sp-progress')) {
        lastOutElem.replaceWith(result);
    } else {
        $el.append(result);
        $el.scrollTop($el.prop("scrollHeight"));
    }
}

function migrationExecuteStep(step_code, postData, succesCallback) {
    postData = postData || {};
    postData['step_code'] = step_code;
    postData['send_sessid'] = $('#migration-container').data('sessid');
    postData['search'] = $('input[name=migration_search]').val();
    postData['filter'] = $('select[name=migration_filter]').val();

    migrationEnableButtons(0);

    jQuery.ajax({
        type: "POST",
        dataType: "html",
        data: postData,
        success: function (result) {
            if (succesCallback) {
                succesCallback(result)
            } else {
                migrationOutLog(result);
            }
        },
        error: function (result) {

        }
    });
}

function migrationEnableButtons(enable) {
    var buttons = $('#migration-container').find('input,select');
    if (enable) {
        buttons.removeAttr('disabled');
    } else {
        buttons.attr('disabled', 'disabled');
    }
}

function migrationMigrationRefresh(callbackAfterRefresh) {
    migrationExecuteStep(
        $('select[name=migration_filter]').val(),
        {},
        function (data) {
            $('#migration_migrations').empty().html(data);
            if (callbackAfterRefresh) {
                callbackAfterRefresh()
            } else {
                migrationEnableButtons(1);
            }
        });
}

function migrationBuilder(postData) {
    var $block = $('[data-builder="' + postData['builder_name'] + '"]');
    migrationExecuteStep('migration_create', postData, function (result) {
        $block.html(result);
    });
}

function migrationScrollList() {
    var $el = $('#migration_migrations');
    $el.scrollTop($el.prop("scrollHeight"));
}

jQuery(document).ready(function ($) {

    $.fn.serializeFormJSON = function () {

        var o = {};
        var a = this.serializeArray();
        $.each(a, function () {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };


    var openblockIx = 0;

    (function () {
        if (localStorage) {
            openblockIx = localStorage.getItem('migrations_open_block');
            openblockIx = (openblockIx) ? parseInt(openblockIx, 10) : 0;
        }

        var $block = $('.sp-block_title').eq(openblockIx).closest('.sp-block');
        $block.addClass('sp-active');
    })();

    migrationMigrationRefresh(function () {
        migrationEnableButtons(1);
        migrationScrollList();
    });

    $('#migration-container').on('change', 'select[name=migration_filter]', function () {
        migrationMigrationRefresh(function () {
            migrationEnableButtons(1);
            migrationScrollList();
            $('#tab_cont_tab1').click();
        });
    });

    $('#migration-container').on('keypress', 'input[name=migration_search]', function (e) {
        if (e.keyCode === 13) {
            migrationMigrationRefresh(function () {
                migrationEnableButtons(1);
                migrationScrollList();
                $('#tab_cont_tab1').click();
            });
        }
    });

    $('#migration-container').on('click', '.sp-search', function () {
        migrationMigrationRefresh(function () {
            migrationEnableButtons(1);
            migrationScrollList();
            $('#tab_cont_tab1').click();
        });
    });

    $('#migration-container').on('click', '.sp-optgroup-check', function (e) {
        var checkboxes = $(this).closest('.sp-optgroup').find(':checkbox');
        checkboxes.attr("checked", !checkboxes.attr("checked"));
        e.preventDefault();
    });

    $('[data-builder]').on('submit', 'form', function (e) {
        e.preventDefault();
        var postData = $(this).serializeFormJSON();
        migrationBuilder(postData);
    });

    $('#migration-container').on('click', '.sp-block_title', function () {
        var $block = $(this).closest('.sp-block');

        $('.sp-block').not($block).removeClass('sp-active');
        $block.addClass('sp-active');

        if (localStorage) {
            openblockIx = $('.sp-block_title').index(this);
            localStorage.setItem('migrations_open_block', '' + parseInt(openblockIx, 10));
        }

        var docViewTop = $(window).scrollTop();
        var elemTop = $block.offset().top;
        if (elemTop <= docViewTop) {
            $(document).scrollTop(elemTop - 25);
        }
    });


});
