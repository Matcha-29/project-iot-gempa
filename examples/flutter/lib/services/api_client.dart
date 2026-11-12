import 'dart:convert';
import 'dart:async';

import 'package:http/http.dart' as http;

class ApiClient {
  ApiClient({http.Client? client}) : _client = client ?? http.Client();

  // Untuk berpindah jaringan, ganti alamat aktif di bawah ini.
  // Sebelumnya: http://192.168.73.142:8000/api
  static const String baseUrl = 'http://192.168.56.1:8000/api';

  final http.Client _client;
  String? _bearerToken;

  void setBearerToken(String? token) {
    _bearerToken = token;
  }

  Future<ApiResponse> login({
    required String identifier,
    required String password,
    String deviceName = 'flutter-app',
  }) {
    return _post('login', {
      'identifier': identifier,
      'kata_sandi': password,
      'device_name': deviceName,
    });
  }

  Future<ApiResponse> activateAccount({
    required String phoneNumber,
    required String activationCode,
    required String password,
  }) {
    return _post('aktivasi', {
      'no_telepon': phoneNumber,
      'kode_aktivasi': activationCode,
      'kata_sandi': password,
    });
  }

  Future<ApiResponse> getRegionStatus() => _get('status-wilayah');

  Future<ApiResponse> getEarthquakeHistory({
    int page = 1,
    int perPage = 20,
  }) {
    if (page < 1 || perPage < 1 || perPage > 100) {
      throw ArgumentError(
          'page must be positive and perPage must be between 1 and 100.');
    }

    return _get('riwayat-gempa', {
      'page': '$page',
      'per_page': '$perPage',
    });
  }

  Future<ApiResponse> submitCitizenResponse({
    required int userId,
    required String status,
    int? detectionId,
  }) {
    if (status != 'AMAN' && status != 'BUTUH_BANTUAN') {
      throw ArgumentError.value(
          status, 'status', 'Must be AMAN or BUTUH_BANTUAN.');
    }

    return _post('respons-warga', {
      'id_pengguna': userId,
      'status_respons': status,
      if (detectionId != null) 'id_deteksi': detectionId,
    });
  }

  Future<ApiResponse> sendTelemetry({
    required bool node1Vibration,
    required bool node2Vibration,
    required bool consensus,
    required double node1PureG,
    required double node1Magnitude,
    required double node2PureG,
    required double node2Magnitude,
    required bool node2Online,
  }) {
    return _post('telemetri/kirim', {
      'node1_vib': node1Vibration,
      'node2_vib': node2Vibration,
      'consensus': consensus,
      'node1_pure_g': node1PureG,
      'node1_mag': node1Magnitude,
      'node2_pure_g': node2PureG,
      'node2_mag': node2Magnitude,
      'node2_online': node2Online,
    });
  }

  Future<ApiResponse> _get(
    String path, [
    Map<String, String> queryParameters = const {},
  ]) {
    final uri = Uri.parse('$baseUrl/$path').replace(
      queryParameters: queryParameters.isEmpty ? null : queryParameters,
    );

    return _send(() => _client.get(uri, headers: _headers));
  }

  Future<ApiResponse> _post(String path, Map<String, Object?> body) {
    final uri = Uri.parse('$baseUrl/$path');

    return _send(
      () => _client.post(
        uri,
        headers: _headers,
        body: jsonEncode(body),
      ),
    );
  }

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (_bearerToken != null) 'Authorization': 'Bearer $_bearerToken',
      };

  Future<ApiResponse> _send(Future<http.Response> Function() request) async {
    final response = await request().timeout(const Duration(seconds: 15));
    final decoded = jsonDecode(response.body);

    if (decoded is! Map<String, dynamic>) {
      throw const FormatException('API returned an unexpected JSON response.');
    }

    final message = decoded['message'] is String
        ? decoded['message'] as String
        : 'Unknown API response.';
    final data = decoded['data'] is Map<String, dynamic>
        ? decoded['data'] as Map<String, dynamic>
        : <String, dynamic>{};

    if (response.statusCode < 200 ||
        response.statusCode >= 300 ||
        decoded['success'] != true) {
      throw ApiException(
        statusCode: response.statusCode,
        message: message,
        data: data,
      );
    }

    return ApiResponse(message: message, data: data);
  }

  void close() {
    _client.close();
  }
}

class ApiResponse {
  const ApiResponse({required this.message, required this.data});

  final String message;
  final Map<String, dynamic> data;
}

class ApiException implements Exception {
  const ApiException({
    required this.statusCode,
    required this.message,
    this.data = const {},
  });

  final int statusCode;
  final String message;
  final Map<String, dynamic> data;

  @override
  String toString() => 'ApiException($statusCode): $message';
}
