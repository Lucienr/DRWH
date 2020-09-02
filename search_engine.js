import {SearchEngine} from './APIService.js'

new Vue({
    el: '#testVue',
    data: {
      text_search: {
        searchText: '',
        synonyme: 0,
        context: 'text',
        certainty: 0,
      },
      advanced_search: {
        patient_age: '',
        origin_code_list: [],
        department_num_list:[],
        title: '',
        document_date: '',
        document_last_nb_days: '',
        encounter_num: '',
        periode_document: '',
        exclure: ''
      },
      // structured_search: {
      //
      // },
      atomic_queries_fulltext: [],
      searched_document_list:[],
    },
    methods: {
      addAtomicQueryFullText() {
        this.atomic_queries_fulltext.push({
            'searchText': this.text_search.searchText,
            'synonyme': this.text_search.synonyme,
            'context': this.text_search.context,
            'certainty': this.text_search.certainty
          })
      },
      addAtomicQueryAdvanced(index) {
        this.atomic_queries_fulltext[i]['advanced_search'] = {
          'patient_age': this.advanced_search.patient_age,
          'origin_code_list': this.advanced_search.origin_code_list,
          'department_num_list':this.advanced_search.department_num_list,
          'title': this.advanced_search.title,
          'document_date': this.advanced_search.document_date,
          'document_last_nb_days': this.advanced_search.document_last_nb_days,
          'encounter_num': this.advanced_search.encounter_num,
          'periode_document': this.advanced_search.periode_document,
          'exclure': this.advanced_search.exclure
        }
      },
      removeAtomicQueryFulltext(index) {
        Vue.delete(this.atomic_queries_fulltext, index);
      },
      sumbitAtomicQuery() {
        let request = {
          'search_query' : [],
          'query_type': "text_search"
        }
        for (let i = 0; i < this.atomic_queries_fulltext.length; i++) {
          if (this.atomic_queries_fulltext[i]['context'] == 'patient')
            this.atomic_queries_fulltext[i]['context'] = 'patient_text'
          request['search_query'].push({'text_search': this.atomic_queries_fulltext[i]})
        }
        console.log(request)
        return SearchEngine.search(request)
          .then(documents =>
            (this.searched_document_list = documents)
          )
      }
    },
    computed: {
    },
    mounted () {
    },
});
