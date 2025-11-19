import 'dart:io';
import '../models/user.dart';

typedef AuthCallback = void Function(User user);

void showRegister(AuthCallback onRegister) {
  stdout.write('Nom : ');
  final nom = stdin.readLineSync() ?? '';
  stdout.write('Prénom : ');
  final prenom = stdin.readLineSync() ?? '';
  stdout.write('Téléphone : ');
  final telephone = stdin.readLineSync() ?? '';
  stdout.write('Mot de passe : ');
  final password = stdin.readLineSync() ?? '';
  stdout.write('Confirmer mot de passe : ');
  final passwordConfirmation = stdin.readLineSync() ?? '';
  stdout.write("Type (client/distributeur) [client] : ");
  var type = stdin.readLineSync();
  if (type == null || (type != 'client' && type != 'distributeur')) type = 'client';
  final user = User(nom: nom, prenom: prenom, telephone: telephone, password: password, passwordConfirmation: passwordConfirmation, type: type);
  onRegister(user);
}

void showVerifyCode(void Function(String tel, String code) onVerify) {
  stdout.write('Téléphone : ');
  final tel = stdin.readLineSync() ?? '';
  stdout.write('Code à 4 chiffres : ');
  final code = stdin.readLineSync() ?? '';
  onVerify(tel, code);
}

void showLogin(void Function(String tel, String pass) onLogin) {
  stdout.write('Téléphone : ');
  final tel = stdin.readLineSync() ?? '';
  stdout.write('Mot de passe : ');
  final pass = stdin.readLineSync() ?? '';
  onLogin(tel, pass);
}
