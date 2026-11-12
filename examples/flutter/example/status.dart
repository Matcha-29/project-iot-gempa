import 'package:earthquake_api_client/services/api_client.dart';

Future<void> main() async {
  final api = ApiClient();

  try {
    final response = await api.getRegionStatus();
    print(response.data);
  } finally {
    api.close();
  }
}
