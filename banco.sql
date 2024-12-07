CREATE OR REPLACE FUNCTION itens_aberto()

CREATE OR REPLACE FUNCTION atualizar_comanda_em_pagamento()
RETURNS TRIGGER AS $$
DECLARE
    total_recebido DECIMAL(10,2);
BEGIN
    IF (TG_OP = 'DELETE') THEN
        SELECT COALESCE(SUM(p.valor), 0)
        INTO total_recebido
        FROM pagamento p 
        JOIN comanda c ON p.id_comanda = c.id
        WHERE p.id_comanda = OLD.id_comanda;

        UPDATE comanda
        SET
            valor_recebido = total_recebido
        WHERE id = OLD.id_comanda;
    ELSE
        SELECT COALESCE(SUM(p.valor), 0)
        INTO total_recebido
        FROM pagamento p 
        JOIN comanda c ON p.id_comanda = c.id
        WHERE p.id_comanda = NEW.id_comanda;

        IF total_recebido > (SELECT valor_total FROM comanda WHERE id = NEW.id_comanda) THEN
            RAISE EXCEPTION 'Valor recebido ira ultrapassar valor total da comanda' USING ERRCODE = 'P0001';
        ELSE
            UPDATE comanda
            SET
                valor_recebido = total_recebido
            WHERE id = NEW.id_comanda;
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;