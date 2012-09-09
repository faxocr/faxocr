#
# Simple makefile for srhelper
#

CC      = gcc
OBJS    = srhelper.o
TARGET  = srhelper

$(TARGET): $(OBJS)
	$(CC) -o $@ $(OBJS)

install:
	cp $(TARGET) ../../bin

srhelper.o: srhelper.c

clean:
	-rm -f a.out *.o $(TARGET)
