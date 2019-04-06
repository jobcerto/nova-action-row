<template>
    <span class="flex justify-end">
        <button  @click.prevent="determineActionStrategy" class="cursor-pointer text-70 hover:text-primary no-underline flex items-center">
            <i :class="field.icon" class="text-lg"></i>
        </button>

        <!-- Action Confirmation Modal -->
        <!-- <portal to="modals"> -->
            <transition name="fade">
                <component
                :is="component"
                :action="action"
                v-if="confirmActionModalOpened"
                :selected-resources="selectedResources"
                :working="working"
                :resource-name="resourceName"
                :errors="errors"
                @confirm="executeAction"
                @close="confirmActionModalOpened = false"
                />
            </transition>
            <!-- </portal> -->
        </span>
    </template>

    <script>
        import _ from 'lodash'

        import { Errors, InteractsWithResourceInformation } from 'laravel-nova'

        export default {
            mixins: [InteractsWithResourceInformation],
            props: ['resourceName', 'field'],

            data: () => ({
                working: false,
                errors: new Errors(),
                confirmActionModalOpened: false,
            }),

            created() {
             _(this.actionFields).each(field => {
                field.fill = () => ''
            })
         },

         mounted() {
            let parentElement = this.$el.parentElement;
            parentElement.classList.add('td-fit');
            parentElement.style.paddingRight = '0px';
        },

        methods: {
        /**
         * Determine whether the action should redirect or open a confirmation modal
         */
         determineActionStrategy() {
            if (this.action.withoutConfirmation) {
                this.executeAction()
            } else {
                this.openConfirmationModal()
            }
        },

        /**
         * Confirm with the user that they actually want to run the selected action.
         */
         openConfirmationModal() {
            this.confirmActionModalOpened = true
        },

        /**
         * Close the action confirmation modal.
         */
         closeConfirmationModal() {
            this.confirmActionModalOpened = false
        },


        /**
         * Execute the selected action.
         */
         executeAction() {
            this.working = true

            Nova.request({
                method: 'post',
                url: `/nova-api/${this.resourceName}/action`,
                params: this.actionRequestQueryString,
                data: this.actionFormData(),
            })
            .then(response => {
                this.confirmActionModalOpened = false
                this.handleActionResponse(response.data)
                this.working = false
            })
            .catch(error => {
                this.working = false

                if (error.response.status == 422) {
                    this.errors = new Errors(error.response.data.errors)
                }
            })
        },

        /**
         * Gather the action FormData for the given action.
         */
         actionFormData() {
            return _.tap(new FormData(), formData => {
                formData.append('resources', this.selectedResources)

                _.each(this.actionFields, field => {
                    field.fill(formData)
                })
            })
        },

        /**
         * Handle the action response. Typically either a message, download or a redirect.
         */
         handleActionResponse(response) {
            if (response.message) {
                this.$emit('actionExecuted')
                this.$toasted.show(response.message, { type: 'success' })
            } else if (response.deleted) {
                this.$emit('actionExecuted')
            } else if (response.danger) {
                this.$emit('actionExecuted')
                this.$toasted.show(response.danger, { type: 'error' })
            } else if (response.download) {
                let link = document.createElement('a')
                link.href = response.download
                link.download = response.name
                document.body.appendChild(link)
                link.click()
                document.body.removeChild(link)
            } else if (response.redirect) {
                window.location = response.redirect
            } else if (response.openInNewTab) {
                window.open(response.openInNewTab, '_blank')
            } else {
                this.$emit('actionExecuted')
                this.$toasted.show(this.__('The action ran successfully!'), { type: 'success' })
            }
        },
    },

    computed: {

          /**
         * Get the query string for an action request.
         */
         actionRequestQueryString() {
            return {
                action: this.action.uriKey,
                filters: 'W10=',
                pivotAction: false,
                search: '',
                trashed: '',
                viaResource: '',
                viaResourceId: '',
                viaRelationship: '',
            }
        },
        actionFields() {
            return this.field.action.fields;
        },

        component() {
            return this.field.action.component
        },

        action() {
            return this.field.action;
        },

        selectedResources() {
          return [this.field.attribute]
      },
  },
}
</script>
