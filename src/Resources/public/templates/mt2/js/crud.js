function CrudManager($scope, config) {
    this.$scope = $scope;
    if ($scope.data('crudDecoupled') === true) {
        this.$formModal = $($scope.data('crudRefFormModal'));
        this.$filterDrawer = $($scope.data('crudRefFilterFormDrawer'));
        this.$newRecordBtn = $($scope.data('crudRefNewRecordBtn'));
        this.$simpleFilter = $($scope.data('crudRefSimpleFilterControl'));
    } else {
        this.$formModal = $scope.find('.modal-form');
        this.$filterDrawer = $scope.find('.modal-filter');
        this.$newRecordBtn = $scope.find('.btn-new-record');
        this.$simpleFilter = $scope.find('.simple-filter');
    }
    this.$formFilter = this.$filterDrawer.find('form');
    this.$btnSaveModal = this.$formModal.find('.btn-save');
    this.$btnNextModal = this.$formModal.find('.btn-next');
    this.$table = $scope.find('.crud-table');
    this.$formatExport = $scope.find('.format-export');

    if (config !== undefined && config.isChained) {
        this.isChained = true;
    }
}

CrudManager.prototype = {
    $scope: null,
    $newRecordBtn: null,
    $formModal: null,
    $filterDrawer: null,
    $formFilter: null,
    $btnOpenFilter: null,
    $simpleFilter: null,
    $btnSaveModal: null,
    $btnNextModal: null,
    $table: null,
    $formatExport: null,
    appendBtn: '.crud-append-btn',
    relativeAppendBtn: '.crud-relative-append-btn',
    collectionRemoveBtn: '.collection-remove-item',
    collectionItem: '.collection-item',
    dt: undefined,
    chainedManager: undefined,
    isChained: false,

    constructor: CrudManager,

    init: function () {
        this.initListeners();
        this.initDataTable();
        return this;
    },

    loadFormModal: function (url) {
        let _this = this;
        _this.toggleBodyBlock()
        $('.modal-body', _this.$formModal).load(url, function (res, statusTxt) {
            _this.toggleBodyBlock();
            if (statusTxt === "success") {
                _this.initComponents(_this.$formModal.find('.modal-body'));
                _this.$formModal.trigger('api_form_modal_loaded');
                _this.$formModal.modal('show');
            } else {
                Swal.fire({
                    text: "Something went wrong while loading the form, please try again later.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }

        });
    },

    loadModal: function ($modal, url) {
        let _this = this;
        _this.toggleBodyBlock()
        $('.modal-body', $modal).load(url, function (res) {
            _this.toggleBodyBlock()
            _this.initComponents($modal.find('.modal-body'));
            $modal.trigger('api_form_modal_loaded');
            $modal.modal('show');
        });
    },

    initListeners: function () {
        let _this = this;
        _this.$newRecordBtn.click(function () {
            _this.loadFormModal($(this).data('load-url'))
        });
        _this.$formatExport.click(function (evt) {
            evt.preventDefault();
            let filterData = _this.$formFilter.serialize();
            let start = _this.dt.page.info().start;
            let length = _this.dt.page.info().length;
            let url = $(this).attr('href') + '?' + filterData + '&start=' + start + '&length=' + length;
            window.open(url);
        });
        _this.$simpleFilter.on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                let targetBinding = $(this).data('binds');
                $(targetBinding, _this.$formFilter).val($(this).val());

                _this.dt.ajax.reload();
                let drawer = KTDrawer.getInstance(_this.$filterDrawer[0]);
                drawer.hide();
            }
        });
        _this.$formFilter.delegate('input', 'keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                _this.dt.ajax.reload();
                let drawer = KTDrawer.getInstance(_this.$filterDrawer[0]);
                drawer.hide();
            }
        });
        _this.$table.delegate('.view-record-btn', 'click', function (e) {
            _this.loadModal(_this.$detailsModal, $(this).data('load-url'));
            e.preventDefault();
            e.stopPropagation();
        });
        _this.$table.delegate('.edit-record-btn', 'click', function (e) {
            _this.loadFormModal($(this).data('load-url'));
            e.preventDefault();
            e.stopPropagation();
        });
        _this.$table.delegate('.remove-record-btn', 'click', function (e) {
            _this.removeRecord($(this));
            e.preventDefault();
            e.stopPropagation();
        });
        _this.$formModal.on('api_form_modal_loaded', function (evt) {
            let $form = _this.$formModal.find('form');
            $form.validate({
                ignore: ":not(:visible),:disabled",
                errorElement: "span",
                errorPlacement: function (error, element) {
                    error.addClass("mt-2 mb-2 text-danger validation-error");
                    if (element.prop("type") === "checkbox") {
                        error.insertAfter(element.parent("label"));
                    } else if (element.prop("type") === "radio") {
                        error.insertAfter(element.parents(".icheck"));
                    } else if (element.is('select') && (element.hasClass('select2') || element.hasClass('select2-ph'))) {
                        error.insertAfter(element.siblings(".select2"));
                    } else if (element.is('textarea') && element.hasClass('lc-ckeditor')) {
                        error.insertAfter(element.siblings(".ck-editor"));
                    } else if (element.is('input') && element.parents('.input-group').length > 0) {
                        error.insertAfter(element.parents('.input-group'));
                    } else {
                        error.insertAfter(element);
                    }
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass("is-invalid").removeClass("is-valid");
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).addClass("is-valid").removeClass("is-invalid");
                },
                submitHandler: function (form) {
                    _this.toggleBodyBlock()
                    $.ajax(
                        {
                            url: $(form).prop('action'),
                            method: 'post',
                            data: $(form).serialize(),
                            success: function (res) {
                                _this.dt.ajax.reload();
                                _this.$formModal.modal('hide');
                                toastr.success('The record has been processed.')
                                $('body').trigger('crud_form_processed', [$(form).prop('name')]);
                            },
                            error: function (res) {
                                $('.modal-body', _this.$formModal).html(res.responseText);
                                _this.initComponents(_this.$formModal.find('.modal-body'));
                                _this.$formModal.trigger('api_form_modal_loaded');
                            },
                            complete: function () {
                                _this.toggleBodyBlock()
                            }
                        }
                    );
                    return false;
                }

            });
            _this.$clientDataContainer = $('.new-client-container', _this.$formModal);
        });
        _this.$btnSaveModal.click(function () {
            let $form = _this.$formModal.find('form');
            if ($form.valid()) {
                $form.trigger('submit')
            }
        });
        _this.$filterDrawer.find('.btn-apply-filter').click(function (evt) {
            _this.dt.ajax.reload();
            let drawer = KTDrawer.getInstance(_this.$filterDrawer[0]);
            drawer.hide();
        });
        _this.$filterDrawer.find('.btn-reset-filter').click(function (evt) {
            _this.$filterDrawer.find('select').each(function (i) {
                $(this).val(null).trigger('change');
            })
        });

        _this.$formModal.delegate(_this.appendBtn, 'click', function (e) {
            _this.appendForm($(this), function ($el) {
                _this.initComponents($el);
                $el.trigger('section-appended');
            }, $(this).data('prototypeName'));
        });
        _this.$formModal.delegate(_this.relativeAppendBtn, 'click', function (e) {
            _this.appendForm($(this), function ($el) {
                _this.initComponents($el);
            }, $(this).data('prototype-name'), $(this).closest('.collection-container').find('.collection-list'));
        });
        _this.$formModal.delegate(_this.collectionRemoveBtn, 'click', function (e) {
            let item = $(this);
            Swal.fire({
                title: el.data('confirmation-msg-title'),
                text: el.data('confirmation-msg-desc'),
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: el.data('confirmation-msg-btn-ok'),
                cancelButtonText: el.data('confirmation-msg-btn-cancel')
            }).then(function (confirmed) {
                if (confirmed.value) {
                    _this.removeCollectionItem(item);
                }
            });

        });
    },

    removeCollectionItem: function ($el) {
        let _this = this;
        $el.closest(_this.collectionItem).addClass('d-none').remove();
    },

    removeRecord: function (el) {
        let _this = this;

        Swal.fire({
            title: el.data('confirmation-msg-title'),
            text: el.data('confirmation-msg-desc'),
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: el.data('confirmation-msg-btn-ok'),
            cancelButtonText: el.data('confirmation-msg-btn-cancel')
        }).then(function (confirmed) {
            if (confirmed.value) {
                _this.toggleBodyBlock()
                $.ajax({
                    url: el.data('action-url'),
                    method: 'delete',
                    data: {
                        token: el.data('token')
                    }
                }).done(function (res) {
                    _this.dt.ajax.reload();
                    _this.$table.trigger('record_removed', [_this.dt]);
                    toastr.success('The record has been removed.')
                }).fail(function (res) {
                    if (res.responseJSON.msg !== undefined) {
                        Swal.fire('Oops!', res.responseJSON.msg, 'error');
                    } else {
                        Swal.fire('Oops!', 'Something went wrong, please try again later.', 'error');
                    }
                }).always(function () {
                    _this.toggleBodyBlock()
                })
            }
        });
    },

    initDataTable: function () {
        let _this = this;
        _this.$table.on('processing.dt', function (e, settings, processing) {
            $('.dataTables_processing').css('display', 'none');
            let target = $(e.currentTarget).closest('.card')[0];
            if (processing) {
                _this.blockUI(target);
            } else {
                _this.unBlockUI(target);
            }
        });
        let orderingColumns = _this.$table.data('ordering-columns');
        _this.dt = _this.$table.DataTable({
            dom: '<"top">rt<"bottom-tbar"<"row p-2"<"col-lg-4"l><"col-lg-8"p>>>',
            paging: true,
            lengthChange: true,
            searching: true,
            info: true,
            language: {
                'zeroRecords': _this.$table.data('empty-table-msg') ? _this.$table.data('empty-table-msg') : 'No data available in table'
            },
            autoWidth: false,
            ajax: {
                url: _this.$table.data('source'),
                method: 'GET',
                data: function (d) {
                    let filterData = _this.$formFilter.serializeArray();
                    if (_this.$formFilter) {
                        let $lengthControl = _this.$formFilter.find('.data-length');
                        let $startControl = _this.$formFilter.find('.data-start');
                        $lengthControl.val(d.length).trigger('change');
                        $startControl.val(d.start).trigger('change');
                    }
                    filterData.push({name: 'length', value: d.length});
                    filterData.push({name: 'start', value: d.start});

                    if (d.order.length > 0) {
                        filterData.push({name: 'order_column', value: orderingColumns[d.order[0].column]});
                        filterData.push({name: 'order_dir', value: d.order[0].dir});
                    } else {

                        filterData.push({name: 'order_column', value: orderingColumns[0]});
                        filterData.push({name: 'order_dir', value: 'desc'});
                    }
                    return filterData;
                },
                dataSrc: function (response) {
                    return response.data;
                }
            },
            ordering: _this.$table.data('ordering-enabled'),
            order: [[0]],
            processing: true,
            serverSide: true,
            drawCallback: function (oSettings) {
                _this.$table.find('[data-toggle="tooltip"]').tooltip();
            },

        });
        if (_this.$table.hasClass('table-select-tool')) {
            _this.$table.on('click', 'tr', function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    _this.$table.trigger('row_deselected', [_this.dt]);
                } else {
                    _this.$table.find('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                    _this.$table.trigger('row_selected', [_this.dt]);
                }
            });

        }

    },

    loadClientData: function (clientId) {
        let _this = this;
        let url = _this.$newRecordBtn.data('load-client-url');
        url = url.replace('_0_', clientId);
        _this.$clientDataContainer.LoadingOverlay("show");
        $('.modal-body', _this.$formModal).load(url, function (res) {
            _this.$clientDataContainer.LoadingOverlay("hide");
            _this.initComponents(_this.$formModal.find('.modal-body'));
            _this.$formModal.trigger('api_form_modal_loaded');
            _this.$formModal.modal('show');
        });
    },

    enableClientForm: function () {
        let _this = this;
        _this.$clientDataContainer.find('input,select').each(function (i) {
            $(this).prop('readonly', false);
            $(this).val('').trigger('change');
            if ($(this)[0].nodeName.toLowerCase() === 'select') {
                $(this).select2({
                    disabled: false
                });
            }
        })
    },

    disableClientForm: function () {
        let _this = this;
        _this.$clientDataContainer.find('input,select').each(function (i) {
            $(this).prop('readonly', true);
            if ($(this)[0].nodeName.toLowerCase() === 'select') {
                $(this).select2({
                    disabled: true
                });
            }
        })
    },

    toggleBodyBlock: function (targetSelector) {
        const target = document.querySelector("body");
        let blockUI = KTBlockUI.getInstance(target);
        if (blockUI !== null) {
            if (blockUI.isBlocked()) {
                blockUI.release();
            } else {
                blockUI.block();
            }
            return;
        }

        blockUI = new KTBlockUI(target, {
            message: '<div class="blockui-message"><span class="spinner-border text-primary"></span> Loading...</div>',
            overlayClass: "bg-primary bg-opacity-25",
        });
        blockUI.block();
    },

    blockUI: function (target) {
        let blockUI = KTBlockUI.getInstance(target);
        if (blockUI === null) {
            blockUI = new KTBlockUI(target, {
                message: '<div class="blockui-message"><span class="spinner-border text-primary"></span> Loading...</div>',
                overlayClass: "bg-primary bg-opacity-25",
            });
            blockUI.block();
        }
        if (!blockUI.isBlocked()) {
            blockUI.block();
        }

    },

    unBlockUI: function (target) {
        let blockUI = KTBlockUI.getInstance(target);
        if (blockUI !== null) {
            blockUI.release();
            return;
        }
    },

    initComponents: function ($scope) {
        $scope = $scope ? $scope : $('body');
        this.initDatepicker($scope);
        this.initDateTimePicker($scope);
        this.initDropdown($scope);
        this.initTooltips($scope);
    },

    initDatepicker: function ($scope) {
        $scope = $scope || $('body');
        let _this = this;
        $scope.find('.widget-datepicker').each(function (index) {
            let changes = $(this).data('dp-defines');
            let definedBy = $(this).data('dp-defined');

            if (changes !== undefined) {
                $(this).on('change', function () {
                    let $changes = $('#' + changes);
                    $changes.val('').trigger('change');
                    $changes.datepicker('setStartDate', _this.getStartDateForDefiner($(this)));
                });
            }

            if ($(this).hasClass('future-disabled')) {
                $(this).datepicker({
                    format: 'M/dd/yyyy',
                    endDate: new Date()
                })
            } else {
                let startDate = definedBy !== undefined ? _this.getStartDateForDefiner($('#' + definedBy)) : null;
                $(this).datepicker({
                    format: 'M/dd/yyyy',
                    startDate: startDate
                })
            }

        });
    },
    initDateTimePicker: function ($scope) {
        let _this = this;
        $scope = $scope || $('body');
        $scope.find('.widget-datetimepicker').each(function (index) {
            let changes = $(this).data('dp-defines');
            let definedBy = $(this).data('dp-defined');

            if (changes !== undefined) {
                $(this).on('change', function () {
                    let $changes = $('#' + changes);
                    $changes.val('').trigger('change');
                    $changes.datetimepicker('setStartDate', _this.getStartDateTimeForDefiner($(this)));
                });
            }
            let startDate, endDate = null;
            if ($(this).hasClass('future-disabled')) {
                endDate = new Date();
            } else if ($(this).hasClass('past-disabled')) {
                startDate = new Date();
            } else {
                let $definedBy = $('#' + definedBy);
                startDate = definedBy !== undefined ? _this.getStartDateTimeForDefiner($definedBy) : null;
            }
            $(this).datetimepicker({
                format: 'M/dd/yyyy hh:ii',
                endDate: endDate,
                startDate: startDate
            })

        });
    },

    getStartDateForDefiner: function (definerCmp) {
        let checkInDate = moment(definerCmp.val(), 'MMM/DD/YYYY');
        checkInDate.add('1', 'days');
        return checkInDate.format('MMM/DD/YYYY');
    },

    getStartDateTimeForDefiner: function (definerCmp) {
        let checkInDate = moment(definerCmp.val(), 'MMM/DD/YYYY hh:ii');
        checkInDate.add('1', 'hours');
        return checkInDate.format('MMM/DD/YYYY hh:ii');
    },

    initDropdown: function ($scope) {
        $scope = $scope || $('body');
        $scope.find('.form-select').each(function (i) {
            $(this).select2({
                width: '100%',
                clear: true,
                placeholder: $(this).attr('placeholder'),
                disabled: $(this).attr('readonly') === 'readonly',
                parent: $scope
            });
        });
    },

    initTooltips: function ($scope) {
        $scope = $scope || $('body');
        $('[data-toggle="tooltip"]', $scope).tooltip();
        $('.do-tooltip', $scope).tooltip();
    },

    appendForm: function ($triggerEl, cb, protoName, list) {
        list = list ? list : $($triggerEl.attr('data-list'));
        let counter = list.data('widget-counter') | list.children().length;
        let widgetTags = list.attr('data-widget-tags');
        protoName = protoName ? protoName : '__name__';
        if (!counter) {
            counter = list.children().length;
        }
        let newWidget = $triggerEl.data('prototype');
        let re = new RegExp(protoName, "g");
        newWidget = newWidget.replace(re, counter);
        widgetTags = widgetTags.replace(re, counter);
        counter++;
        list.data('widget-counter', counter);
        let newElem = $(widgetTags).html(newWidget);
        let counterClass = 'odd';
        if (counter % 2 === 0) {
            counterClass = 'even';
        }
        newElem.addClass(counterClass).addClass('d-none');
        if ($triggerEl.data('policy') === 'insert-before') {
            $triggerEl.parents('li').before(newElem)
        } else {
            newElem.appendTo(list);
            newElem.removeClass('d-none')
        }
        cb($(newElem))
    },

    initValidation: function ($form, handlerCb, successCb, failureCb) {
        let _this = this;
        $form.validate({
            ignore: ":not(:visible),:disabled",
            errorElement: "span",
            errorPlacement: function (error, element) {
                error.addClass("mt-2 mb-2 text-danger validation-error");
                if (element.prop("type") === "checkbox") {
                    error.insertAfter(element.parents(".checkbox"));
                } else if (element.prop("type") === "radio") {
                    error.insertAfter(element.parents(".icheck"));
                } else if (element.is('select') && (element.hasClass('select2') || element.hasClass('select2-ph'))) {
                    error.insertAfter(element.siblings(".select2"));
                } else if (element.is('textarea') && element.hasClass('lc-ckeditor')) {
                    error.insertAfter(element.siblings(".ck-editor"));
                } else if (element.is('input') && element.parents('.input-group').length > 0) {
                    error.insertAfter(element.parents('.input-group'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass("is-invalid").removeClass("is-valid");
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).addClass("is-valid").removeClass("is-invalid");
            },
            submitHandler: function (form) {
                if (handlerCb) {
                    handlerCb(form)
                } else {
                    $.LoadingOverlay("show");
                    $.ajax(
                        {
                            url: $(form).prop('action'),
                            method: 'post',
                            data: $(form).serialize(),
                            success: function (res) {
                                successCb(res);
                            },
                            error: function (res) {
                                failureCb(res);
                            },
                            complete: function () {
                                $.LoadingOverlay("hide");
                            }
                        }
                    );
                }
                return false;
            }

        });
    },

};

(function () {
    $('.crud-scope').each(function () {
        (new CrudManager($(this))).init();
    })
})(jQuery, window, document);

module.exports = CrudManager;
