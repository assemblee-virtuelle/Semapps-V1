FROM openjdk:8-alpine

RUN wget https://github.com/jmvanel/semantic_forms/releases/download/2.0/semantic_forms_play-2.0.zip -O temp.zip \
    && unzip temp.zip

RUN apk update && apk add bash nano

RUN mv semantic_forms_play-1.0-SNAPSHOT semantic_forms \
    && cd semantic_forms \
    && cp scripts/start.sh . \
    && chmod +x start.sh \
    && chmod +x bin/semantic_forms_play

WORKDIR /semantic_forms

COPY ./entrypoint.sh /usr/local/bin/

CMD entrypoint.sh