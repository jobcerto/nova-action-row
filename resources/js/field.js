Nova.booting((Vue, router, store) => {
    Vue.component('index-action-row', require('./components/IndexField'))
    Vue.component('detail-action-row', require('./components/DetailField'))
    Vue.component('form-action-row', require('./components/FormField'))
})
