import {Cohort} from './APIService.js'


new Vue({
    el: '#testVue',
    data: {
        search_cohorts: '',
        cohorts: [],
        cohort_results: [],
        newCohort: null,
        selectedCohort: null,
        cohort_merge_one: null,
        cohort_merge_two: null,
        cohort_merge_title: '',
        cohort_merge_desc: '',
        new_cohort_title: '',
        new_cohort_description: '',
        new_cohort_datamart: '',
    },
    methods: {
      createCohort() {
        // let date = Date.now()
        let data = {
          'title':this.new_cohort_title,
          'description_cohort':this.new_cohort_description,
        }
        return Cohort.create(data)
          .then(result => {
            this.getCohort(result.cohort)
              .then(cohort =>{
                this.getCohortList()
                this.showCohortResult(this.selectedCohort)
              })
          });
      },
      deleteCohort(cohort) {
        return Cohort.delete(cohort.cohort_num).then((result) => {
          this.selectedCohort = null
          this.cohort_results = []
          this.getCohortList()
        })
      },
      mergeCohort() {
        return Cohort.merge_cohorts(this.cohort_merge_title, this.cohort_merge_desc, this.cohort_merge_one.cohort_num, this.cohort_merge_two.cohort_num)
          .then((data) => {
            this.getCohort(data.cohort)
              .then(cohort => {
                this.getCohortList()
                this.showCohortResult(this.selectedCohort)
              })
          });
      },
      getCohort(cohortPk) {
        return Cohort.retrieve(cohortPk)
          .then(cohort => {
            this.selectedCohort = cohort
          })
      },
      getCohortResult(cohortPk) {
        return Cohort.cohorts_result(cohortPk)
      },
      getCohortList() {
        return Cohort.list().then(cohorts =>
          (this.cohorts = cohorts)
        );
      },
      showCohortResult(cohort) {
        console.log(cohort)
        this.getCohortResult(cohort.cohort_num)
          .then((data) => {
              this.selectedCohort = cohort
              this.cohort_results = data
          })
      }
    },
    computed: {
      filteredCohorts() {
          return this.cohorts.filter(cohort => {
            return cohort.title.toLowerCase().includes(this.search_cohorts.toLowerCase())
          })
      },
      filteredPatientsIncluded() {
        return this.cohort_results.filter(cohort_result => {
          return cohort_result.status == 1
        })
      },
      filteredPatientsExcluded() {
        return this.cohort_results.filter(cohort_result => {
          return cohort_result.status == 0
        })
      },
      filteredPatientsDoubt() {
        return this.cohort_results.filter(cohort_result => {
          return cohort_result.status == 2
        })
      }
    },
    mounted () {
        this.getCohortList()
    },
});
