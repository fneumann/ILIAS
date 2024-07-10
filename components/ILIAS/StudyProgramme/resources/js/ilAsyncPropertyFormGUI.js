/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

 (function ($) {
  $.ilAsyncPropertyForm = {
    global_config: {
      error_message_template: null,
      async_form_name: 'async_form',
      alert_class: 'alert',
      save_commands: ['save', 'update', 'confirmedDelete'],
      cancel_commands: ['cancel', 'cancelDelete'],
    },
  };

  $.fn.extend({
    ilAsyncPropertyForm(options) {
      const settings = $.extend($.ilAsyncPropertyForm.global_config, options);

      const element = this;

      /**
             * Determine if a certain command is a save command
             *
             * TODO: find better way to determine is its a save command
             */
      const is_save_cmd = function (cmd) {
        return $.inArray(cmd, settings.save_commands);
      };

      /**
             * Determine if a certain command is a cancel command
             *
             * TODO: find better way to determine is its a cancel command
             */
      const is_cancel_cmd = function (cmd) {
        return $.inArray(cmd, settings.cancel_commands);
      };

      /**
             * Sends ajax request form-action
             * Displays error messages if fields are not valid and triggers async_form-events
             *
             * @param actionurl
             * @param formData
             */
      const save_form_data = function (action_target, form_data, form_reference) {
        $.ajax({
          url: action_target,
          type: 'post',
          dataType: 'json',
          data: form_data,
          success(response, status, xhr) {
            // try {
            if (response) {
              // error on while saving
              if (is_save_cmd(response.cmd) !== -1 && response.success === false && $.isArray(response.errors)) {
                $('body').trigger('async_form-error', {
                  message: response.message, errors: response.errors, cmd: response.cmd, form: form_reference,
                });

                // saving was successful
              } else if (is_save_cmd(response.cmd) !== -1 && response.success === true) {
                $('body').trigger('async_form-success', { message: response.message, cmd: response.cmd, form: form_reference });

                // cancel was clicked
              } else if (is_cancel_cmd(response.cmd) !== -1) {
                $('body').trigger('async_form-cancel', { cmd: response.cmd, form: form_reference });
              }
            }
            /* } catch (error) {
                         console.log("The AJAX-response for the async form " + form.attr('id') + " is not JSON. Please check if the return values are set correctly: " + error);
                         } */
          },
        });
      };

      /**
             * Listen to validation errors and display them on the form
             *
             * @param event
             * @param data
             */
      const handle_form_validation = function (event, data) {
        const { form } = data;
        const { errors } = data;

        // remove set error messages
        form.find(`div.${settings.alert_class}`).remove();

        let i; let
          message;
        for (i = 0; i < errors.length; i++) {
          message = settings.error_message_template.replace('[TXT_ALERT]', errors[i].message);

          // TODO: might need a more specific selector
          $(`#${errors[i].key}`).after(message);
        }
      };

      /**
             * Serialize the form data
             *
             * @param form
             * @param submit_button
             * @returns {*}
             */
      const serialize_form_data = function (form, submit_button) {
        const formData = form.serializeArray();
        formData.push({ name: $(submit_button).attr('name'), value: $(submit_button).val() });
        return formData;
      };

      /**
             * Setup async forms
             * Overrides submit-button behaviors, collects the form data and calls the save_form_data function
             */
      const setup_async_form = function () {
        console.log('update async form');
        $(element).find(`form[name='${settings.async_form_name}'] :submit`).each(function () {
          $(this).on('click', function (e) {
            e.preventDefault();

            const form = $(this).closest('form');
            const actionurl = form.attr('action');
            const form_data = serialize_form_data(form, $(this));

            save_form_data(actionurl, form_data, form);
          });
        });
      };

      // setup on load
      setup_async_form();

      // Event handlers

      /**
             * Handle validation messages
             */
      $('body').on('async_form-error', handle_form_validation);

      /**
             * Update form event-handlers if ajax request occurs
             * Only updates, if the request data-type is html (json-request are ignored)
             */
      $(document).ajaxComplete((event, xhr, settings) => {
        // only update search function if ajax-request returns html
        if (settings.dataType === 'html') {
          setup_async_form();
        }
      });

      // only reload if modal with ajax content is loaded
      /* $(element).on('loaded.bs.modal', function () {
                setup_async_form();
            }); */

      return element;
    },
  });
}(jQuery));
