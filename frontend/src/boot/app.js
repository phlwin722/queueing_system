import {
    Notify,
    Dialog
} from 'quasar'

import {
    axios
} from 'boot/axios'

import { axiosClient } from 'src/axiosClient'

const $axios = axiosClient

const $API_BACKEND = "http://192.168.70.240:8080"
const $notify = (color, icon, message, timeout = 5000) => {
    Notify.create({
        icon,
        color,
        message,
        timeout,
        position:'top',
        actions: [
            {
                size : 'xs',
                color: 'white',
                label: 'x',
                round: true,
                handler : () => {}
            }
        ]
    })
}

export {
    $axios,
    $notify,
    Dialog,
    $API_BACKEND
}