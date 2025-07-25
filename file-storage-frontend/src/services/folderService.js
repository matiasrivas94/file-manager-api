import api from './api';

export default {
  getAll() {
    return api.get('/folders');
  },
  create(data) {
    return api.post('/folders', data);
  },
  update(id, data) {
    return api.put(`/folders/${id}`, data);
  },
  remove(id) {
    return api.delete(`/folders/${id}`);
  }
};
