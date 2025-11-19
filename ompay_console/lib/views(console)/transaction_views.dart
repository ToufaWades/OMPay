import 'dart:io';
import '../models/transaction.dart';

void showTransactionList(List<Transaction> transactions) {
  print('--- Transactions ---');
  for (final tx in transactions) {
    print('ID: ${tx.id} | Type: ${tx.type} | Montant: ${tx.montant} | Date: ${tx.dateTransaction} | Status: ${tx.status}');
  }
}

void showTransfert(void Function(String numero, double montant) onTransfert) {
  stdout.write('Numéro destinataire : ');
  final numero = stdin.readLineSync() ?? '';
  stdout.write('Montant : ');
  final montant = double.tryParse(stdin.readLineSync() ?? '0') ?? 0.0;
  onTransfert(numero, montant);
}

void showPaiement(void Function(double montant) onPaiement) {
  stdout.write('Montant : ');
  final montant = double.tryParse(stdin.readLineSync() ?? '0') ?? 0.0;
  onPaiement(montant);
}
