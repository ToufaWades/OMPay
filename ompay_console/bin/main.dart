import 'dart:io';
import '../lib/config/apiService.dart';
import '../lib/config/baseUrl.dart';
import '../lib/models/user.dart';
import '../lib/models/compte.dart';
import '../lib/models/transaction.dart';
import '../lib/services/auth_service.dart';
import '../lib/services/compte_service.dart';
import '../lib/services/transaction_service.dart';
import '../lib/views(console)/auth_views.dart';
import '../lib/views(console)/compte_views.dart';
import '../lib/views(console)/transaction_views.dart';

void main() async {
  final api = ApiService();
  final authService = AuthService(api);
  final compteService = CompteService(api);
  final transactionService = TransactionService(api);
  String? token;
  int? compteId;

  while (true) {
    print('\n=== MENU OMPay ===');
    print('1 - Créer utilisateur');
    print('2 - vérification code');
    print('3 - Connexion');
    print('4 - Dashboard');
    print('5 - Voir solde');
    print('6 - Transactions (a-transfert, b-paiement, c-lister)');
    print('7 - Déconnexion');
    print('0 - Quitter');
    stdout.write('Choix : ');
    final choix = stdin.readLineSync();
    switch (choix) {
      case '1':
        showRegister((user) async {
          final res = await authService.register(user);
          if (res.data != null && res.data['success'] == true) {
            print(res.data['message']);
            if (res.data['code_pin'] != null) {
              print('Votre code PIN : ${res.data['code_pin']}');
            }
          } else {
            print('Erreur : ${res.data}');
          }
        });
        break;
      case '2':
        showVerifyCode((tel, code) async {
          final res = await authService.verifyCode(tel, code);
          print(res.data);
        });
        break;
      case '3':
        showLogin((tel, pass) async {
          final res = await authService.login(tel, pass);
          if (res.data != null && res.data['token'] != null) {
            token = res.data['token'];
            print('Connexion réussie !');
          } else {
            print('Erreur : ${res.data}');
          }
        });
        break;
      case '4':
        if (token == null) {
          print('Veuillez vous connecter.');
          break;
        }
        final res = await compteService.dashboard(token!);
        if (res.data != null) {
          final compte = Compte.fromJson(res.data['compte']);
          showDashboard(compte);
          compteId = compte.id;
        } else {
          print('Erreur : ${res.data}');
        }
        break;
      case '5':
        if (token == null || compteId == null) {
          print("Connectez-vous et accédez au dashboard d'abord.");
          break;
        }
        final res = await compteService.solde(compteId, token!);
        if (res.data != null) {
          showSolde(res.data['solde'], res.data['devise']);
        } else {
          print('Erreur : ${res.data}');
        }
        break;
      case '6':
        if (token == null || compteId == null) {
          print("Connectez-vous et accédez au dashboard d'abord.");
          break;
        }
        print('a - Transfert');
        print('b - Paiement');
        print('c - Lister transactions');
        stdout.write('Choix : ');
        final txChoix = stdin.readLineSync();
        switch (txChoix) {
          case 'a':
            showTransfert((numero, montant) async {
              final res = await transactionService.transfert(compteId!, numero, montant, token!);
              print(res.data);
            });
            break;
          case 'b':
            showPaiement((montant) async {
              final res = await transactionService.paiement(compteId!, montant, token!);
              print(res.data);
            });
            break;
          case 'c':
            final res = await transactionService.list(compteId!, token!);
            if (res.data != null && res.data is List) {
              final txs = (res.data as List).map((e) => Transaction.fromJson(e)).toList();
              showTransactionList(txs);
            } else {
              print('Erreur : ${res.data}');
            }
            break;
          default:
            print('Choix invalide.');
        }
        break;
      case '7':
        token = null;
        compteId = null;
        print('Déconnecté.');
        break;
      case '0':
        print('Bye !');
        exit(0);
      default:
        print('Choix invalide.');
    }
  }
}
