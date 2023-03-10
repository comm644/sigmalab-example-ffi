#define FFI_SCOPE "libfeature"
#define FFI_LIB "./libfeature.so"


typedef void( *Callback)(const void *userData, const char *payload);

void processWithCallback(const void* userData, const char* payload, Callback callback);
