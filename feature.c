#include "feature-ffi.h"

#define EXPORT __attribute__((visibility("default")))
#define IMPORT

EXPORT void processWithCallback(const void* userData, const char* payload, Callback callback)
{
	callback(userData, payload);
}

