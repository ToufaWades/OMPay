import '../config/apiService.dart';
import 'package:dio/dio.dart';

class TransactionService {
  final ApiService api;
  TransactionService(this.api);

  Future<Response> transfert(int compteId, String numero, double montant, String token) async {
    return await api.post('/comptes/$compteId/transfert',
      data: {'numero': numero, 'montant': montant},
      headers: {'Authorization': 'Bearer $token'});
  }

  Future<Response> paiement(int compteId, double montant, String token) async {
    return await api.post('/comptes/$compteId/paiement',
      data: {'montant': montant},
      headers: {'Authorization': 'Bearer $token'});
  }

  Future<Response> list(int compteId, String token) async {
    return await api.get('/comptes/$compteId/transactions', headers: {'Authorization': 'Bearer $token'});
  }
}
