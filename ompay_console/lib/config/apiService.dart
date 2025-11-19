import 'package:dio/dio.dart';
import 'baseUrl.dart';

class ApiService {
  final Dio _dio;
  ApiService([Dio? dio]) : _dio = dio ?? Dio();

  Future<Response<T>> get<T>(String endpoint, {Map<String, dynamic>? queryParameters, Map<String, dynamic>? headers}) async {
    return await _dio.get<T>(
      BaseUrl.api + endpoint,
      queryParameters: queryParameters,
      options: Options(headers: headers),
    );
  }

  Future<Response<T>> post<T>(String endpoint, {dynamic data, Map<String, dynamic>? headers}) async {
    return await _dio.post<T>(
      BaseUrl.api + endpoint,
      data: data,
      options: Options(headers: headers),
    );
  }

  Future<Response<T>> put<T>(String endpoint, {dynamic data, Map<String, dynamic>? headers}) async {
    return await _dio.put<T>(
      BaseUrl.api + endpoint,
      data: data,
      options: Options(headers: headers),
    );
  }

  Future<Response<T>> patch<T>(String endpoint, {dynamic data, Map<String, dynamic>? headers}) async {
    return await _dio.patch<T>(
      BaseUrl.api + endpoint,
      data: data,
      options: Options(headers: headers),
    );
  }

  Future<Response<T>> delete<T>(String endpoint, {dynamic data, Map<String, dynamic>? headers}) async {
    return await _dio.delete<T>(
      BaseUrl.api + endpoint,
      data: data,
      options: Options(headers: headers),
    );
  }
}
