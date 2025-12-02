import axios from "axios";

export default axios.create({
  baseURL: "http://localhost/project/api",
  headers: {
    "Content-Type": "application/json"
  }
});
