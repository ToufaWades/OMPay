class Transaction {
  final int id;
  final String type;
  final double montant;
  final String status;
  final String dateTransaction;
  final String? numeroDestinataire;

  Transaction({
    required this.id,
    required this.type,
    required this.montant,
    required this.status,
    required this.dateTransaction,
    this.numeroDestinataire,
  });

  factory Transaction.fromJson(Map<String, dynamic> json) => Transaction(
    id: json['id'],
    type: json['type'] ?? '',
    montant: double.tryParse(json['montant'].toString()) ?? 0.0,
    status: json['status'] ?? '',
    dateTransaction: json['date_transaction'] ?? '',
    numeroDestinataire: json['numero_destinataire'],
  );
}
