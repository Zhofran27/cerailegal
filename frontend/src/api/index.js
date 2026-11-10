import http, { payload } from "./http";

export const api = {
  statistics: async () => payload(await http.get("/statistics")),
  eligibility: async (answers) =>
    payload(await http.get("/eligibility-check", { params: answers })),
  courts: async (params = {}) => payload(await http.get("/courts", { params })),
  faqs: async (params = {}) => payload(await http.get("/faqs", { params })),
  login: async (data) => payload(await http.post("/login", data)),
  register: async (data) => payload(await http.post("/register", data)),
  me: async () => payload(await http.get("/me")),
  logout: () => http.post("/logout"),
  cases: async () => payload(await http.get("/cases")),
  case: async (id) => payload(await http.get(`/cases/${id}`)),
  adminCases: async (params = {}) =>
    payload(await http.get("/admin/cases", { params })),
  adminCase: async (id) => payload(await http.get(`/admin/cases/${id}`)),
  updateAdminCase: async (id, data) =>
    payload(await http.put(`/admin/cases/${id}/status`, data)),
  adminDocument: (id) =>
    http.get(`/admin/documents/${id}`, { responseType: "blob" }),
  createCase: async (data) => payload(await http.post("/cases", data)),
  submitCase: async (id) => payload(await http.post(`/cases/${id}/submit`)),
  uploadDocument: async (id, file, type) => {
    const body = new FormData();
    body.append("file", file);
    body.append("type", type);
    return payload(await http.post(`/cases/${id}/documents`, body));
  },
  downloadDocument: (id) =>
    http.get(`/documents/${id}/download`, { responseType: "blob" }),
  estimate: async (params) =>
    payload(await http.get("/cases/cost-estimate", { params })),
};
