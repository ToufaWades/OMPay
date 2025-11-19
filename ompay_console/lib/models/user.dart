class User {
  final String nom;
  final String prenom;
  final String telephone;
  final String password;
  final String passwordConfirmation;
  final String type;
  final String? code;

  User({
    required this.nom,
    required this.prenom,
    required this.telephone,
    required this.password,
    required this.passwordConfirmation,
    this.type = 'client',
    this.code,
  });

  factory User.fromJson(Map<String, dynamic> json) => User(
    nom: json['nom'] ?? '',
    prenom: json['prenom'] ?? '',
    telephone: json['telephone'] ?? '',
    password: json['password'] ?? '',
    passwordConfirmation: json['password_confirmation'] ?? '',
    type: json['type'] ?? 'client',
    code: json['code'],
  );

  Map<String, dynamic> toJson() => {
    'nom': nom,
    'prenom': prenom,
    'telephone': telephone,
    'password': password,
    'password_confirmation': passwordConfirmation,
    'type': type,
    if (code != null) 'code': code,
  };
}
