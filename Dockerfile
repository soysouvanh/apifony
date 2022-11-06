FROM alpine:3.14

CMD ["echo", "Hello world!"]

ADD . /apifony/
WORKDIR /apifony