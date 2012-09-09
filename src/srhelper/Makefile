#
# Simple makefile for srhelper
#

CC      = gcc
OBJS    = srhelper.o
TARGET  = srhelper

$(TARGET): $(OBJS)
	$(CC) -o $@ $(OBJS)

srhelper.o: srhelper.c

clean:
	-rm -f a.out *.o $(TARGET)
