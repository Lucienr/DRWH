import {Protocol} from './APIService.js'
import {User} from './APIService.js'
import {MapProtocolPatient} from './APIService.js'
import {Cohort} from './APIService.js'

Vue.component('vue-multiselect', window.VueMultiselect.default)
var Multiselect = VueMultiselect.Multiselect

new Vue({
    el: '#protocols',
    data: {
        protocols: [],
        search_protocols: '',
        search_patients: '',
        search_cohorts: '',
        search_oppositions: '',
        selectedProtocol: null,
        filterOpposition: false,
        selectedCohort: null,
        cohorts: [],
        protocolCohorts: {
            columns: [],
            rows: []
        },

        patientsToAdd: '',
        adding: false,
        addResults: null,
        removedResults: null,
        opposedResults: null,
        oppositions: [],
        editingTitle: false,
        editingDesc: false,
        users: [],
        // for paginated vue-good-table
        maps: {
            columns: [],
            rows: []
        },
        totalMaps: 0,
        serverParams: {
            offset: 0,
            limit: 10,
        },
        isLoading: false,
    },
    methods: {
        editTitle() {
            Protocol.update(this.selectedProtocol);
            this.editingTitle = false;
        },

        editDesc() {
            Protocol.update(this.selectedProtocol);
            this.editingDesc = false;
        },

        addPatients() {
            this.adding=true;
            let ipps = this.patientsToAdd.split("\n")
            let data = {
                "protocol": this.selectedProtocol.id,
                "ipps": ipps,
            }
            Protocol.add_patients(this.selectedProtocol.id, data).then((result) => {
                this.adding=false;
                this.addResults = result;
                this.addResults.invalid_ipps_str = this.addResults.invalid_ipps.join(", ")
                this.addResults.not_included_ipps_str = this.addResults.not_included_ipps.join(", ")
            }).then(this.loadMaps(this.selectedProtocol));
        },

        removePatients() {
            this.adding=true;
            let ipps = this.patientsToAdd.split("\n")
            let data = {
                "protocol": this.selectedProtocol.id,
                "ipps": ipps,
            }
            Protocol.remove_patients(this.selectedProtocol.id, data).then((result) => {
                this.adding=false;
                this.removedResults = result;
                this.removedResults.invalid_ipps_str = this.removedResults.invalid_ipps.join(", ")
                this.removedResults.not_included_ipps_str = this.removedResults.not_included_ipps.join(", ")
            }).then(this.loadMaps(this.selectedProtocol));
        },

        registerOppositions() {
            this.adding=true;
            let ipps = this.patientsToAdd.split("\n")
            let data = {
                "protocol": this.selectedProtocol.id,
                "ipps": ipps,
            }
            Protocol.register_oppositions(this.selectedProtocol.id, data).then((result) => {
                this.adding=false;
                this.opposedResults = result;
                this.opposedResults.invalid_ipps_str = this.opposedResults.invalid_ipps.join(", ")
            }).then(this.loadMaps(this.selectedProtocol));
        },
        
        loadProtocolCohorts(protocol) {
            Protocol.cohorts(protocol.id).then(data => {
                this.protocolCohorts.rows = data;
                this.protocolCohorts.columns = [
                    {"label": "Titre", "field": "cohort_data.title"},
                    {"label": "Associé le", "field": "created_date"},
                    {"label": "Inclus dans la cohorte", "field": "cohort_data.nb_patients"},
                    {"label": "Dernière synchronisation le", "field": "last_sync_date"},
                    {"label": "Actions", "field": "actions"},
                ]
            });
        },
        
        associateCohort() {
            let newAssociation = {
                "cohort": this.selectedCohort.id,
                "protocol": this.selectedProtocol.id
            }
            Protocol.associate(this.selectedProtocol.id, newAssociation).then(data => {
                console.log(data)
                this.showProtocol(this.selectedProtocol);
            });
        },

        syncCohort(cohort) {
            Protocol.sync(this.selectedProtocol.id, cohort).then(data => {
                console.log(data);
                cohort = data;
                this.showProtocol(this.selectedProtocol)
            });
        },

        showProtocol(protocol) {
            this.selectedProtocol=protocol;
            this.loadMaps(protocol);
            this.loadProtocolCohorts(protocol);
        },
    
        getMaps(protocol) {
            return MapProtocolPatient.list({protocol: protocol.id})
        },

        filteredMaps() {
            return this.notOpposedMaps.filter(map => {
              return map.patient.master_ipp.toLowerCase().includes(this.search_patients.toLowerCase()) 
              || map.patient.firstname.toLowerCase().includes(this.search_patients.toLowerCase()) 
              || map.patient.lastname.toLowerCase().includes(this.search_patients.toLowerCase()) 
            })
        },
        
        filteredOppositions() {
            return this.opposedMaps.filter(map => {
              return map.patient.master_ipp.toLowerCase().includes(this.search_oppositions.toLowerCase()) 
              || map.patient.firstname.toLowerCase().includes(this.search_oppositions.toLowerCase()) 
              || map.patient.lastname.toLowerCase().includes(this.search_oppositions.toLowerCase()) 
            })
        },
        userFullName(option) {
            return option.firstname + ' ' + option.lastname;
        },
        updateProtocol() {
            Protocol.update(this.selectedProtocol).then(data => this.selectedProtocol=data);
        },

        // for paginated vue-good-table
        updateParams(newProps) {
            this.serverParams = Object.assign({}, this.serverParams, newProps);
        },
        onFilterChange() {
            if (this.filterOpposition) {
                this.updateParams({is_opposed: true});
            } else {
                delete this.serverParams.is_opposed
            }
            this.loadMaps(this.selectedProtocol);
        },
        onPageChange(params) {
            this.updateParams({offset: (params.currentPage-1) * params.currentPerPage});
            this.loadMaps(this.selectedProtocol);
        },
  
        onPerPageChange(params) {
            this.updateParams({limit: params.currentPerPage});
            console.log(this.serverParams)
            this.loadMaps(this.selectedProtocol);
        },
  
        loadMaps(protocol) {
            this.serverParams.protocol = protocol.id;

            return MapProtocolPatient.list(this.serverParams).then(response => {
                this.totalMaps = response.count;
                this.maps.rows = response.results;
                this.maps.columns = [
                    {"label": "IPP", "field": "patient.master_ipp"},
                    {"label": "Initials", "field": "patient.initials"},
                    {"label": "Date de naissance", "field": "patient.birth_date"},
                    {"label": "Sexe", "field": "patient.sex"},
                    {"label": "Inclu le", "field": "register_date"},
                    {"label": "Inclu par", "field": "register_by"},
                    {"label": "Opposition", "field": "opposition"},
                    {"label": "Actions", "field": "actions"},
                ]
            });
        },

        updateMap(map, type) {
            if (type === 'opposed') {
                map.is_opposed = !map.is_opposed;
            }            
            MapProtocolPatient.update(map).then(this.loadMaps(this.selectedProtocol));
        },

        deleteMap(map) {
            MapProtocolPatient.destroy(map.id).then(this.loadMaps(this.selectedProtocol));
        },

        updateProtocolStatus(status) {
            this.selectedProtocol.status = status;
            this.updateProtocol();
        }
      
    },
    computed: {
        filteredProtocols() {
            return this.protocols.filter(protocol => {
              return protocol.title.toLowerCase().includes(this.search_protocols.toLowerCase()) 
              || protocol.reference.toLowerCase().includes(this.search_protocols.toLowerCase()) 
            })
        },
        opposedMaps() {
            return this.maps.rows.filter(map => {
                return map.is_opposed
            })
        },
        notOpposedMaps() {
            return this.maps.rows.filter(map => {
                return !map.is_opposed
            })
        },
    },

    mounted () {
        Protocol.list().then((data) => {
            this.protocols = data;
            this.showProtocol(data[0]);
        });
        User.list().then((data) => {
            this.users = data;
        })
        Cohort.list().then(data => {
            this.cohorts = data;
        })
    },

    filters: {
        moment: function(date) {
            return moment(date).format('DD/MM/YYYY')
        },
        not_empty: function(info) {
            return info ? info : 'inconnu'
        },
        user: function(info) {
            return info ? info.firstname + ' ' + info.lastname : 'inconnu'
        },
        opposition_by: function(row) {
            if (row.opposition_source === 'drwh') {
                let info = row.opposition_by
                let user = info ? info.firstname + ' ' + info.lastname : 'inconnu'
                return 'par ' + user
            } else {
                return 'sur la plateforme patient'
            }
        },
        source: function(info) {
            return info === 'import' ? info : 'Cohorte ' + info
        },
    },
    components: {
        Multiselect
    },

});
