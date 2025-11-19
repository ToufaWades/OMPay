import 'dart:io';
import '../models/compte.dart';

void showDashboard(Compte compte) {
  print('--- Dashboard ---');
  print('Numéro compte : ${compte.numeroCompte}');
  print('Solde : ${compte.solde} ${compte.devise}');
}

void showSolde(double solde, String devise) {
  print('Solde : $solde $devise');
}
