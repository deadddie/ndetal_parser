/**
 *  ndetal.com parser
 *
 *  @author deadie
 */

'use strict';

var parser = {
    ajaxUrl: 'ajax.php',

    info: $('.info'),
    log: $('.log'),
    bar: $('.progress-bar'),
    percent: $('.progress-bar-current'),
    percentValue: $('.percent-value'),
    totalValue: $('.info-total-value'),

    progressPiece: 0,
    progressPiecePercent: 0,

    currentPage: 1, // текущая страница
    currentPosition: 2, // текущая позиция в выходной файле
    pagesCount: 0, // общее количество страниц
    stopped: false,

    row: 2, // первая строка в файле-шаблоне
    productId: 1, // начальный ID товаров
    total: 0, // общее количество товаров

    debug: false,
    debugPagesCount: 6,

    /**
     * Инициализация.
     */
    init: function() {
        this.currentPage = 1;
        this.currentPosition = 2;
        this.progressPiece = 0;
        this.progressPiecePercent = 0;

        this.stopped = false;

        this.row = 2;
        this.productId = 1;
        this.total = 0;

        this.info.find('p a').remove();

        this.clearLog();
        this.bindEvents();
    },

    /**
     * Установка обработчиков событий.
     */
    bindEvents: function() {
        $(document)
            .on('click', '.info .start', function () {
                parser.startParse();
            })
            .on('click', '.info .stop', function () {
                parser.stopParse();
            });
    },

    /**
     * Запуск парсинга.
     */
    startParse: function() {
        var xhr = $.ajax({
            url: this.ajaxUrl,
            type: 'post',
            dataType: 'json',
            data: {
                method: 'startParse'
            }
        })
        .done(function (pagesCount) {
            var pages = parser.debug ? parser.debugPagesCount : pagesCount;
            parser.pagesCount = pages;
            parser.progressPiece = parser.getProgressPiece(pages);
            parser.progressPiecePercent = 100 / pages;

            while (parser.currentPage <= pages) {
                if (!parser.stopped) {
                    var parsed = parser.processPage(parser.currentPage);
                    var row = parser.row;
                    var productId = parser.productId;
                    parser.saveToTemp(parsed, row, productId);
                    parser.currentPage++;
                } else {
                    xhr.abort();
                }
            }
            parser.logger('Обработка завершена.');

            parser.logger('Сохранение файла...');
            var output = parser.stopParse();
            parser.logger('Файл успешно сохранен.');

            // Выводим ссылку на скачивание файла
            var file = '&nbsp;<a href="'+ output +'" download target="_blank">Скачать файл</a>';
            parser.info.find('p').append(file);
        });
    },

    /**
     * Остановка парсинга.
     *
     * @returns {*}
     */
    stopParse: function() {
        var output;
        this.stopped = true;
        var xhr = $.ajax({
            url: this.ajaxUrl,
            type: 'post',
            dataType: 'json',
            data: {
                method: 'stopParse'
            },
            async: false
        })
        .done(function (data) {
            output = data;
        });
        return output;
    },

    /**
     * Обработка страницы.
     *
     * @param page
     */
    processPage: function(page) {
        var output;
        var xhr = $.ajax({
            url: this.ajaxUrl,
            type: 'post',
            dataType: 'json',
            data: {
                method: 'processPage',
                page: page
            },
            async: false
        })
        .done(function (data) {
            output = data;
        });
        return output;
    },

    /**
     * Сохранение обработанных данных во временный файл.
     *
     * @param parsed
     * @param row
     * @param productId
     */
    saveToTemp: function(parsed, row, productId) {
        var xhr = $.ajax({
            url: this.ajaxUrl,
            type: 'post',
            dataType: 'json',
            data: {
                method: 'saveToTemp',
                data: {
                    parsed: parsed,
                    row: row,
                    product_id: productId
                }
            },
            async: false
        })
        .done(function (count) {
            parser.total = parser.total + count;
            parser.row = parser.row + count;
            parser.productId = parser.productId + count;
            parser.progress();
            parser.logger('Обработана страница #' + parser.currentPage + '. Добавлено ' + count + ' товаров.');
        });
    },

    /**
     * Прогресс-бар.
     */
    progress: function() {
        parser.percent.width(parser.percent.width() + parser.progressPiece);
        var currentPercent = parseFloat(parser.percentValue.text()) + parser.progressPiecePercent;
        if (currentPercent > 100) {
            currentPercent = 100;
        }
        parser.percentValue.text(currentPercent.toFixed(2));
        parser.totalValue.text(parser.total);
    },

    /**
     * Извлечение "кусочка" прогресса.
     *
     * @param pagesCount
     * @returns {number}
     */
    getProgressPiece: function(pagesCount) {
        return parser.bar.width() / pagesCount;
    },

    /**
     * Добавление строки в экранный лог.
     * @param log
     */
    logger: function(log) {
        var logString = '<div class="log-item">'+ log +'</div>';
        parser.log.prepend(logString);
    },

    /**
     * Очистка логаы.
     */
    clearLog: function () {
        parser.log.empty();
    }
};

parser.init();