import React, { useEffect, useRef, useState } from 'react'
import ReactDOM from 'react-dom/client'
import { Fragment } from "react"
import useSWR from 'swr'
import axios from 'axios'

import '../assets/css/address_book.sass'

interface Page {
    genHeader: number,
    list: {
        email: string,
        gen: number,
        nm: string,
        phonenum: string,
        bday: string,
        major: string,
        yr: number,
        tmi: string,
        editable: boolean
    }[]
}

type DataPage = 'left' | 'right'

interface EditInfo {
    dataPage: DataPage | undefined,
    idx: number,
    email: string,
    tmi: string
}

interface ReqInfo {
    val: number,
    pp: number,
    mode: number,
}

type Data = {
    [index in DataPage]: Page
} & {
    leftNum: number
    rightNum: number
    lastPage: boolean
}

ReactDOM.createRoot(document.getElementById('root')!).render(
    <React.StrictMode>
        <AddressBook />
    </React.StrictMode>,
)

function AddressBook() {
    const gen = Number(document?.getElementById('gen')?.getAttribute('value'));

    const [reqParam, setReqParam] = useState<ReqInfo>({ val: gen, pp: 10, mode: 0 })
    const [editInfo, setEditInfo] = useState<EditInfo>({ dataPage: undefined, idx: 0, email: '', tmi: '' })
    const [leftShown, setLeftShown] = useState(true)
    const ref = useRef<HTMLInputElement>(null)

    const fetcher = ([url, reqParam]: [string, ReqInfo]) => axios.post(url, reqParam)
        .then(response => response.data)
        .catch(error => console.log(error))
    const { mutate, data } = useSWR<Data, any, [string, ReqInfo]>(['api/address_book', reqParam], fetcher)

    console.log(data)

    useEffect(() => {
        ref.current?.focus()
    }, [editInfo.dataPage, editInfo.idx, editInfo.email])

    function setupTmiInput(dataPage: DataPage, idx: number, email: string, tmi: string) {
        setEditInfo({ dataPage, idx, email, tmi })
    }

    function checkBlur(e: React.KeyboardEvent) {
        if (e.key === 'Enter') {
            ref?.current?.blur()
        }
    }

    const requestUpdateTmi = async (email: string, tmi: string) => axios.post('api/tmi', { email, tmi }).then(() => true).catch(() => false)
    async function updateTmi() {
        const { dataPage, idx, email, tmi } = editInfo
        if (!data || !dataPage) return

        const success = await requestUpdateTmi(email, tmi)

        // Take appropriate action (error msg print) later
        if (!success) return

        setEditInfo({ dataPage: undefined, idx: 0, email: '', tmi: '' })
        const newState = {
            left: {
                genHeader: data?.left.genHeader,
                list: [...data?.left.list]
            },
            right: {
                genHeader: data?.right.genHeader,
                list: [...data?.right.list]
            },
            leftNum: data?.leftNum,
            rightNum: data?.rightNum,
            lastPage: data.lastPage
        }

        if (dataPage) newState[dataPage].list[idx].tmi = tmi

        mutate(newState)
    }

    return (
        <Fragment>
            <div className="phonebook-tab">
                <button className="border selected"><i className="bi bi-telephone-fill"></i></button>
                <button className="border"><i className="bi bi-bookmark-star-fill"></i></button>
                <button className="border"><i className="bi bi-search"></i></button>
            </div>
            <div className="phonebook-wrapper border">
                {
                    data && (['left', 'right'] as DataPage[]).map((d: DataPage, di) =>
                        <div id={`page-${d}`} className={((d === 'left') !== !leftShown) ? 'shown' : ''} key={di}>
                            {
                                !data[d] ?
                                    <div className="blank-page">빈 페이지입니다.</div> :
                                    <Fragment>
                                        {
                                            data[d].genHeader !== 0 && <div className="gen-header">{data[d].genHeader}기</div>
                                        }
                                        {
                                            data[d].list.map((e, i) => {
                                                const editing = (editInfo.dataPage === d && editInfo.idx === i);

                                                return (
                                                    <div className="person-container border-bottom" key={i}>
                                                        <div>
                                                            <div>{e.gen}기 {e.nm}</div>
                                                            <a href={`tel:${e.phonenum}`}> {e.phonenum}</a>
                                                        </div>
                                                        <div>
                                                            <div><i className="bi bi-calendar-fill"></i> {e.bday}</div>
                                                            <div><i className="bi bi-mortarboard-fill"></i> {e.major}, {e.yr}학번</div>
                                                        </div>
                                                        <div className={"tmi-wrapper" + (e.tmi ? "" : " notmi")}>
                                                            {
                                                                editing ?
                                                                    <input maxLength={40} value={editInfo.tmi} ref={ref}
                                                                        onChange={e => setEditInfo(v => ({ dataPage: v.dataPage, idx: v.idx, email: v.email, tmi: e.target.value }))}
                                                                        onKeyUp={checkBlur}
                                                                        onBlur={updateTmi}
                                                                    />
                                                                    : <div>{e.tmi ? e.tmi : '비고 항목이 없습니다.'}</div>

                                                            }
                                                            {e.editable && !editing && <i className="bi bi-pencil-fill" title="수정" onClick={() => setupTmiInput(d, i, e.email, e.tmi)}></i>}
                                                        </div>
                                                    </div>)
                                            })
                                        }
                                        {
                                            Array(reqParam.pp - data[d].list.length).fill(0).map((_, i) => <div className="person-container border-bottom" key={i}></div>)
                                        }
                                    </Fragment>
                            }
                        </div>
                    )
                }
                <div id="page-left-idx" className={"border-top" + (leftShown ? " shown" : "")}>
                    <button onClick={() => { setReqParam({ mode: 1, pp: 10, val: data?.leftNum ? data?.leftNum - 2 : 0 }); setLeftShown(false) }} disabled={data?.leftNum === 0}><i className="bi bi-chevron-compact-left"></i></button>
                    <div>{data && data?.leftNum + 1}</div>
                    <button className="fake-btn" onClick={() => setLeftShown(false)} disabled={data?.leftNum === 0 || !data?.right}>
                        <i className="bi bi-chevron-compact-right"></i>
                    </button>
                </div>
                <div id="page-right-idx" className={"border-top" + (!leftShown ? " shown" : "")}>
                    {
                        data?.right &&
                        <Fragment>
                            <button className="fake-btn" onClick={() => setLeftShown(true)}><i className="bi bi-chevron-compact-left"></i></button>
                            <div>{data && data?.rightNum + 1}</div>
                            <button onClick={() => { setReqParam({ mode: 1, pp: 10, val: data?.rightNum + 1 }); setLeftShown(true) }} disabled={data?.lastPage}>
                                <i className="bi bi-chevron-compact-right"></i>
                            </button>
                        </Fragment>
                    }

                </div>
            </div>
        </Fragment>
    )
}