#!/bin/sh
#
# Create a distributable archive of the current version of Makeself

VER=`cat VERSION`
mkdir -p /tmp/makeself-$VER release
cp -a makeself* test README.md COPYING VERSION .gitmodules /tmp/makeself-$VER/

# create a temporary named pipe
PIPE=$(mktemp -u)
echo "pipe:$PIPE"
mkfifo $PIPE
# attach it to file descriptor 3
exec 3<>$PIPE
# unlink the named pipe
rm $PIPE
echo "coucou" >&3

./makeself.sh --ssl-encrypt --ssl-pass-src "fd:3" --notemp /tmp/makeself-$VER release/makeself-$VER.run "Makeself v$VER" echo "Makeself has extracted itself" 

