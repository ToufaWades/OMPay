import '../config/apiService.dart';
import '../models/user.dart';
import 'package:dio/dio.dart';

class AuthService {
  final ApiService api;
  AuthService(this.api);

  Future<Response> register(User user) async {
    return await api.post('/auth/register', data: user.toJson());
  }

  Future<Response> verifyCode(String telephone, String code) async {
    return await api.post('/auth/verify', data: {'telephone': telephone, 'code': code});
  }

  Future<Response> login(String telephone, String password) async {
    return await api.post('/auth/connexion', data: {'telephone': telephone, 'password': password});
  }
}
