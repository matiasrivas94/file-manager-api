import api from './api';

export default {
  getAll(params = {}) {
    return api.get('/files', { params });
  },
  upload(formData, onUploadProgress) {
    return api.post('/files', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress,
    });
  },
  remove(id) {
    return api.delete(`/files/${id}`);
  },
  update(id, data) {
    return api.post(`/files/${id}?_method=PUT`, data);
  },
  restore(id) {
    return api.post(`/files/${id}/restore`);
  },
  forceDelete(id) {
    return api.delete(`files/${id}/force`);
  },
};
