/**
 * Excalidraw AMD module for Moodle integration.
 *
 * @module     mod_excalidraw/app
 * @copyright  2025 Excalidraw Moodle Plugin
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification'], function($, Ajax, Notification) {

    var excalidrawAPI = null;
    var config = null;
    var autoSaveInterval = null;

    /**
     * Initialize Excalidraw.
     *
     * @param {Object} initConfig Configuration object
     */
    function init(initConfig) {
        config = initConfig;

        // Wait for Excalidraw library to load.
        waitForExcalidraw(function() {
            initializeExcalidraw();
            setupSaveButton();
            setupAutoSave();
        });
    }

    /**
     * Wait for Excalidraw to be available.
     *
     * @param {Function} callback Function to call when ready
     */
    function waitForExcalidraw(callback) {
        if (typeof window.ExcalidrawLib !== 'undefined') {
            callback();
        } else {
            setTimeout(function() {
                waitForExcalidraw(callback);
            }, 100);
        }
    }

    /**
     * Initialize Excalidraw component.
     */
    function initializeExcalidraw() {
        var container = document.getElementById('excalidraw-container');
        if (!container) {
            console.error('Excalidraw container not found');
            return;
        }

        var initialData = null;
        if (config.drawingdata) {
            try {
                initialData = JSON.parse(config.drawingdata);
            } catch (e) {
                console.error('Error parsing drawing data:', e);
            }
        }

        // Create React element and render Excalidraw.
        var excalidrawElement = window.React.createElement(
            window.ExcalidrawLib.Excalidraw,
            {
                initialData: initialData,
                onChange: function(elements, state) {
                    // Store reference for saving.
                },
                ref: function(api) {
                    excalidrawAPI = api;
                }
            }
        );

        window.ReactDOM.render(excalidrawElement, container);
    }

    /**
     * Setup save button.
     */
    function setupSaveButton() {
        $('#excalidraw-save-btn').on('click', function(e) {
            e.preventDefault();
            saveDrawing(true);
        });
    }

    /**
     * Setup auto-save functionality.
     */
    function setupAutoSave() {
        // Auto-save every 30 seconds.
        autoSaveInterval = setInterval(function() {
            saveDrawing(false);
        }, 30000);
    }

    /**
     * Save drawing to Moodle.
     *
     * @param {Boolean} showNotification Whether to show notification
     */
    function saveDrawing(showNotification) {
        if (!excalidrawAPI) {
            return;
        }

        try {
            var elements = excalidrawAPI.getSceneElements();
            var appState = excalidrawAPI.getAppState();

            var drawingData = {
                elements: elements,
                appState: {
                    viewBackgroundColor: appState.viewBackgroundColor,
                    currentItemFontFamily: appState.currentItemFontFamily,
                    currentItemFontSize: appState.currentItemFontSize,
                    currentItemStrokeColor: appState.currentItemStrokeColor,
                    currentItemBackgroundColor: appState.currentItemBackgroundColor,
                    currentItemFillStyle: appState.currentItemFillStyle,
                    currentItemStrokeWidth: appState.currentItemStrokeWidth,
                    currentItemRoughness: appState.currentItemRoughness,
                    currentItemOpacity: appState.currentItemOpacity
                }
            };

            var statusEl = $('#excalidraw-status');
            if (showNotification) {
                statusEl.text('Saving...');
            }

            $.ajax({
                url: M.cfg.wwwroot + '/mod/excalidraw/save.php',
                method: 'POST',
                data: {
                    excalidrawid: config.excalidrawid,
                    content: JSON.stringify(drawingData),
                    sesskey: config.sesskey
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (showNotification) {
                            statusEl.text(M.util.get_string('saved', 'mod_excalidraw')).css('color', 'green');
                            setTimeout(function() {
                                statusEl.text('');
                            }, 3000);
                        }
                    } else {
                        if (showNotification) {
                            statusEl.text('Error: ' + response.error).css('color', 'red');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    if (showNotification) {
                        statusEl.text('Error saving drawing').css('color', 'red');
                        console.error('Save error:', error);
                    }
                }
            });
        } catch (e) {
            console.error('Error saving drawing:', e);
        }
    }

    return {
        init: init
    };
});
