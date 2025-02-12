jQuery(document).ready(function($) {
    console.log("Free Mail SMTP Email Router JS loaded");
    $('.add-router-condition , #add-router-condition-button').on('click', function(e) {
        e.preventDefault();
        $('#router-modal').show();
    });
    
    $(document).on('click', '.modal-close', function(e) {
        e.preventDefault();
        $(this).closest('.modal').hide();
    });

    let currentEditId = null;

    function closeModal(saved) { 
        if(!saved){
            if (confirm('Are you sure you want to close? Any unsaved changes will be lost.')) {
                $('#router-modal').hide();
                resetForm();
            }
        } else{
            $('#router-modal').hide();
            resetForm();
        }
       
    }

    function resetForm() {
        currentEditId = null;
        $('#routerLabel').val('');
        $('#conditions').empty();
        $('#connectionToggle').prop('checked', false);
        $('#emailInfoToggle').prop('checked', false);
        $('#connectionSelect').val('');
        $('#emailInfoContent input').val('');
        addCondition(); 
    }

    function collectFormData() {
        const formData = {
            label: $('#routerLabel').val(),
            conditions: getConditionsData(),
            connection: {
                enabled: $('#connectionToggle').is(':checked'),
                selected: $('#connectionSelect').val()
            },
            email: {
                enabled: $('#emailInfoToggle').is(':checked'),
                email: $('#emailInfoContent input[type="email"]').val(),
                name: $('#emailInfoContent input[type="text"]').val()
            },
            id: $('#condition_id').val()
        };
        if (currentEditId) {
            formData.id = currentEditId;
        }

        return formData;
    }

    function getConditionsData() {
        const conditions = [];
        
        $('.condition-row').each(function() {
            const condition = {
                field: $(this).find('.field-select').val(),
                operator: $(this).find('.operator-select').last().val(),
                value: $(this).find('.value-input').val()
            };
            
            const logicalOperator = $(this).closest('.condition-container').find('.operator-select').first().val();
            if (logicalOperator) {
                condition.logical_operator = logicalOperator;
            }
            
            conditions.push(condition);
        });

        console.log('Collected conditions:', conditions.toString());
        return conditions;
    }

    function saveRouter() {
        const formData = collectFormData();
        if(!formData.id){
            formData.is_enabled = false;
        }

        if (!validateForm(formData)) {
            return;
        }
        console.log('Saving form data:', formData);
        $.ajax({
            url: FreeMailSMTPEmailRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'save_email_router',
                data: formData,
                nonce: FreeMailSMTPEmailRouter.nonce  
            },
            success: function(response) {
                if (response.success) {
                    closeModal(true);
                    location.reload();
                } else {
                    alert('Error saving: ' + response.data.message);
                }
            },
            error: function() {
                alert('Server error occurred while saving');
            }
        });
    }

    function validateForm(formData) {
        if (!formData.label.trim()) {
            alert('Please enter a Router Label');
            return false;
        }
        if (formData.conditions.length === 0) {
            alert('Please add at least one condition');
            return false;
        }
        if (formData.connection.enabled && !formData.connection.selected) {
            alert('Please select a connection or disable the section');
            return false;
        }
        if (formData.email.enabled && (!formData.email.email || !formData.email.name)) {
            alert('Please complete email details or disable the section');
            return false;
        }
        return true;
    }

    let conditionCount = 0;
    const fields =  ['To', 'Subject', 'Message', 'From Email', 'From Name', 'CC', 'BCC', 'Reply To'];
    const operators = ['Is', 'Is not', 'Contains', 'Does not Contain', 'Start with', 'End with', 'Regex Match', 'Regex Not Match', 'Is Empty', 'Is Not Empty'];

    function createSelect(options, className, onchange) {
        const select = document.createElement('select');
        select.className = className;
        options.forEach(option => {
            const opt = document.createElement('option');
            opt.value = option.toLowerCase().replace(/ /g, '_');
            opt.textContent = option;
            select.appendChild(opt);
        });
        if (onchange) {
            select.onchange = onchange;
        }
        return select;
    }

    function addCondition() {
        const container = document.getElementById('conditions');
        const conditionContainer = document.createElement('div');
        conditionContainer.className = 'condition-container';

        if (container.children.length > 0) {
            const operatorSelect = createSelect(['AND', 'OR'], 'operator-select');
            operatorSelect.onchange = updateGrouping;
            conditionContainer.appendChild(operatorSelect);
        }

        const conditionRow = document.createElement('div');
        conditionRow.className = 'condition-row';
        conditionRow.id = `condition-${conditionCount}`;

        const numberSpan = document.createElement('span');
        numberSpan.className = 'condition-number';
        numberSpan.textContent = container.children.length + 1;
        conditionRow.appendChild(numberSpan);

        const fieldSelect = createSelect(fields, 'field-select');
        const operatorSelect = createSelect(operators, 'operator-select');
        const valueInput = document.createElement('input');
        valueInput.type = 'text';
        valueInput.className = 'value-input';

        const removeButton = document.createElement('i');
        removeButton.className = 'material-icons delete-icon';
        removeButton.textContent = 'delete';
        removeButton.onclick = () => removeConditionContainer(conditionContainer);

        conditionRow.appendChild(fieldSelect);
        conditionRow.appendChild(operatorSelect);
        conditionRow.appendChild(valueInput);
        conditionRow.appendChild(removeButton);

        conditionContainer.appendChild(conditionRow);
        container.appendChild(conditionContainer);
        conditionCount++;

        updateGrouping();
    }

    function updateGrouping() {
        const containers = Array.from(document.querySelectorAll('.condition-container'));
        const conditionsDiv = document.getElementById('conditions');

        while (conditionsDiv.firstChild) {
            conditionsDiv.firstChild.remove();
        }

        let currentOrGroup = null;
        let currentAndGroup = null;

        containers.forEach((container, index) => {
            const operator = container.querySelector('.operator-select')?.value?.toLowerCase();

            if (index === 0 || operator === 'or') {
                currentAndGroup = null;
                currentOrGroup = document.createElement('div');
                currentOrGroup.className = 'or-group';
                conditionsDiv.appendChild(currentOrGroup);
            }

            if (operator === 'and' && !currentAndGroup) {
                currentAndGroup = document.createElement('div');
                currentAndGroup.className = 'and-group';
                currentOrGroup.appendChild(currentAndGroup);
            }

            const targetContainer = currentAndGroup || currentOrGroup;
            targetContainer.appendChild(container);
        });

        updateConditionNumbers();
    }

    function updateConditionNumbers() {
        const numbers = document.querySelectorAll('.condition-number');
        numbers.forEach((num, index) => {
            num.textContent = index + 1;
        });
    }

    function removeConditionContainer(container) {
        container.remove();
        updateGrouping();
    }

    function setupToggles() {
        const connectionToggle = $('#connectionToggle');
        const emailInfoToggle = $('#emailInfoToggle');
        const connectionContent = $('#connectionContent');
        const emailInfoContent = $('#emailInfoContent');

        connectionToggle.on('change', function(){
            connectionContent.toggleClass('disabled', !connectionToggle.is(':checked'));
        });
        emailInfoToggle.on('change', function(){
            emailInfoContent.toggleClass('disabled', !emailInfoToggle.is(':checked'));
        });
    }

    $('.toggle-header').on('click', function() {
        const target = $( $(this).data('target') );
        target.slideToggle();
        const indicator = $(this).find('.toggle-indicator');
        indicator.text(indicator.text() === 'arrow_drop_down' ? 'arrow_right' : 'arrow_drop_down');
    });

    addCondition();
    setupToggles();

    $(document).keydown(function(e) {
        if (e.key === "Escape" && $('#router-modal').is(':visible')) {
            closeModal(false);
        }
    });

    $(document).on('change', '.toggle-is-enabled', function() {
        const conditionId = $(this).data('id');
        const newStatus = $(this).is(':checked') ? 1 : 0;
        $.ajax({
            url: FreeMailSMTPEmailRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'update_email_router_status',
                condition_id: conditionId,
                status: newStatus,
                nonce: FreeMailSMTPEmailRouter.nonce
            },
            success: function(response) {
                if(response.success) {
                    console.log('Status updated successfully');
                } else {
                    alert('Failed to update status: ' + response.data.message);
                }
            },
            error: function() {
                alert('Server error occurred while updating status');
            }
        });
    });

    $(document).on('click', '.edit-condition', function() {
        const conditionId = $(this).data('id');
        $.ajax({
            url: FreeMailSMTPEmailRouter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_email_router_condition',
                condition_id: conditionId,
                nonce: FreeMailSMTPEmailRouter.nonce
            },
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data);
                    $('#router-modal').show();
                } else {
                    alert('Error retrieving condition: ' + response.data.message);
                }
            },
            error: function() {
                alert('Server error occurred while retrieving condition data');
            }
        });
    });
    
    $(document).on('click', '.delete-condition', function() {
        const conditionId = $(this).data('id');
        if (confirm('Are you sure you want to delete this condition?')) {
            $.ajax({
                url: FreeMailSMTPEmailRouter.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'delete_email_router_condition',
                    condition_id: conditionId,
                    nonce: FreeMailSMTPEmailRouter.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete condition: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Server error occurred while deleting condition');
                }
            });
        }
    });

    function populateEditForm(condition) {
        currentEditId = condition.id; 
        resetForm();
        $('#routerLabel').val(condition.condition_label);
        $('#conditions').empty();
        conditionCount = 0;
        
        try {
            let conditionData = condition.condition_data;
            
            if (typeof conditionData === 'string') {
                conditionData = JSON.parse(conditionData);
            }
            console.log('Parsed condition data:', conditionData);

            if (Array.isArray(conditionData)) {
                conditionData.forEach((item, index) => {
                    addCondition();
                    const $lastRow = $('#conditions').find('.condition-row').last();
                    const $container = $lastRow.closest('.condition-container');
                    
                    if (index > 0) {
                        const $operatorSelect = $container.find('.operator-select').first();
                        if ($operatorSelect.length) {
                            const logicalOp = item.logical_operator || 'and';
                            $operatorSelect.val(logicalOp.toLowerCase());
                        }
                    }
                    
                    $lastRow.find('.field-select').val(item.field);
                    $lastRow.find('.operator-select').last().val(item.operator); 
                    $lastRow.find('.value-input').val(item.value);
                });
                
                updateGrouping();
            }

            if (condition.connection_id) {
                $('#connectionToggle').prop('checked', true).trigger('change');
                $('#connectionSelect').val(condition.connection_id);
            } else {
                $('#connectionToggle').prop('checked', false).trigger('change');
            }

            if (condition.forced_senderemail) {
                $('#emailInfoToggle').prop('checked', true).trigger('change');
                $('#emailInfoContent input[type="email"]').val(condition.forced_senderemail);
                $('#emailInfoContent input[type="text"]').val(condition.forced_sendername);
            } else {
                $('#emailInfoToggle').prop('checked', false).trigger('change');
            }

            if(condition.id){
                $('#condition_id').val(condition.id);
            }
            
        } catch (error) {
            console.error('Error populating form:', error);
            alert('Error loading condition data');
        }
    }

    window.FreeMailSMTPRouter = {
        closeModal,
        addCondition,
        saveRouter,
        resetForm
    };

});