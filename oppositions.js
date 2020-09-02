import {MapProtocolPatient, Patient, PatientOpposition} from './APIService.js'

new Vue({
    el: '#oppositions',
    data: {
        searchIpp: '',
        patientOppositions: [],
        patient: null,
        patientMaps: [],
        maps: [],
        notFound: false,
        searchOppositions: '',
        searchMaps: '',
    },
    methods: {
        async getPatientOppositions() {
            this.patientOppositions = await PatientOpposition.list();
        },
        async searchPatient() {
            this.searchIpp = this.searchIpp.split(' ').join('').split('&').join('')
            if(this.searchIpp != '') {
                const data = await Patient.list({"ipp": this.searchIpp})
                if(data.length > 0) {
                    this.notFound = false;
                    this.patient = data[0];
                    this.getPatientMaps();
                } else {
                    this.notFound = true;
                }
            } else {
                this.notFound = true;
            }
        },
        async registerPatientOpposition(patient) {
            let object = {
                "patient": patient.patient_num,
            }
            const data = await PatientOpposition.create(object)
            this.patientOppositions.push(data)
        },
        async cancelPatientOpposition(opposition, index) {
            const data = await PatientOpposition.destroy(opposition.id)
            this.patientOppositions.splice(index, 1);
            this.searchOppositions = '';
        },
        async getPatientMaps() {
            const data = await MapProtocolPatient.list({"patient": this.patient.patient_num});
            this.patientMaps = data;
        },
        async updateMap(map) {
            map.is_opposed = !map.is_opposed;
            const data = await MapProtocolPatient.update(map);
            map.opposition_date = data.opposition_date;
            map.opposition_source = data.opposition_source;
            await this.getOpposedMaps();

        },
        async getOpposedMaps() {
            this.maps = await MapProtocolPatient.list({"is_opposed": true, limit: 1000, offset:0});
            this.maps = this.maps.results;
        },
        
    },
    mounted() {
        this.getPatientOppositions();
        this.getOpposedMaps();
    },
    filters: {
        moment: function(date) {
            return moment(date).format('DD/MM/YYYY')
        },
        user: function(info) {
            return info ? info.firstname + ' ' + info.lastname : 'inconnu'
        },
        not_empty: function(info) {
            return info ? info : 'inconnu'
        },
    },
    computed: {
        patientOpposedMaps() {
            return this.patientMaps.filter(map => {
              return map.is_opposed
            })
        },
        patientNotOpposedMaps() {
            return this.patientMaps.filter(map => {
              return !map.is_opposed
            })
        },
        filteredPatientOppositions() {
            return this.patientOppositions.filter(opposition => {
                return opposition.master_ipp.toLowerCase().includes(this.searchOppositions.toLowerCase()) 
            })
        },
        filteredMaps() {
            return this.maps.filter(map => {
                return map.patient.master_ipp.toLowerCase().includes(this.searchMaps.toLowerCase()) 
            })
        },
    }
});
