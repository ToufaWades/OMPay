class Compte {
  final int id;
  final String numeroCompte;
  final double solde;
  final String devise;

  Compte({
    required this.id,
    required this.numeroCompte,
    required this.solde,
    required this.devise,
  });

  factory Compte.fromJson(Map<String, dynamic> json) => Compte(
    id: json['id'],
    numeroCompte: json['numero_compte'] ?? '',
    solde: double.tryParse(json['solde'].toString()) ?? 0.0,
    devise: json['devise'] ?? 'FCFA',
  );
}
