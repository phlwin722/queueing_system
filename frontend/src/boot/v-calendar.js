import { boot } from 'quasar/wrappers'
import { setupCalendar, Calendar, DatePicker } from 'v-calendar'
import 'v-calendar/style.css'

export default boot(({ app }) => {
  app.use(setupCalendar, {})
  app.component('VCalendar', Calendar)
  app.component('VDatePicker', DatePicker)
})