import { createApp } from "vue";
import { createPinia } from "pinia";
import "./style.css";
import App from "./App.vue";
import router from "./router.js";
import "./echo";
import "@fortawesome/fontawesome-free/css/all.min.css";

const app = createApp(App).use(createPinia()).use(router).mount("#app");
