.PHONY: tests

output := lib
lib := $(output)/libfeature.so

all: lib tests

lib: $(lib)

$(lib): feature.c
	test -e lib || mkdir lib;
	gcc -shared $< -o $@


tests:
	cd lib ; pwd; php -d opcache.enable_cli=1 \
		-d opcache.preload=./../preload.php \
		-f ../tests/testFfiExampleClass.php