import axios from 'axios';
export default function apiClient(url: string){
    return axios.create({
        baseURL: url,
        withCredentials: true,
    })
}
