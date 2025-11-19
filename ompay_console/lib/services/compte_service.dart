import '../config/apiService.dart';
import 'package:dio/dio.dart';

class CompteService {
  final ApiService api;
  CompteService(this.api);

  Future<Response> dashboard(String token) async {
    return await api.get('/api/compte', headers: {'Authorization': 'Bearer $token'});
  }

  Future<Response> solde(int compteId, String token) async {
    return await api.get('/comptes/$compteId/solde', headers: {'Authorization': 'Bearer $token'});
  }
}
