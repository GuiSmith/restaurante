DROP FUNCTION atualizar_comanda_em_pagamento;

CREATE OR REPLACE FUNCTION atualizar_comanda_em_pagamento()
RETURNS TRIGGER AS $$
DECLARE
    var_comanda REGISTER;
BEGIN

    IF (TG_OP = 'DELETE') THEN
        UPDATE comanda
        SET
            valor_recebido = valor_recebido - OLD.valor
        WHERE id = OLD.id_comanda;
    ELSE

        SELECT c.valor_recebido, c.valor_total
        INTO var_comanda
        FROM comanda AS c
        WHERE c.id = NEW.id_comanda

        IF var_comanda.valor_recebido + NEW.valor > var_comanda.valor_total THEN
            RAISE EXCEPTION 'Valor recebido ira ultrapassar valor total da comanda' USING ERRCODE = 'P0001';
        ELSE
            UPDATE comanda
            SET valor_recebido = valor_recebido + NEW.valor
            WHERE comanda.id = NEW.id_comanda;
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;