import axios from 'axios';

axios.defaults.baseURL = window.apiUrl ? window.apiUrl : 'http://corp.tularegion.local/local/api/indicators';

export default axios;
